<?php

namespace App\Fogger\Data\Mongo;

use App\Fogger\Mask\Exception\UnknownMaskException;
use App\Fogger\Mask\MaskStrategyInterface;
use App\Fogger\Mask\MaskStrategyProvider;
use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

class DataCopier
{
    private $client;

    private $maskProvider;

    public function __construct(Client $client, MaskStrategyProvider $maskProvider)
    {
        $this->client = $client;
        $this->maskProvider = $maskProvider;
    }

    /**
     * @param ChunkMessage $message
     * @throws InvalidJsonException
     * @throws UnknownMaskException
     */
    public function copyDataChunk(ChunkMessage $message)
    {
        $source = $message->getSource();
        $target = $message->getTarget();
        $suffix = $message->getSuffix();
        $collectionName = $message->getCollection()->getName();
        $targetCollection = $collectionName.$suffix;

        $sourceCollection = $this->client->$source->$collectionName;
        $targetCollection = $this->client->$target->$targetCollection;
        $cursor = $sourceCollection->find(
            [
                '_id' => [
                    '$in' => $message->getKeys(),
                ],
            ]
        );
        $masked = [];

        /** @var BSONDocument $document */
        foreach ($cursor as $document) {
            $array = $this->convertToArray($document);
            foreach ($message->getCollection()->getMasks() as $path => $maskDefinition) {
                    $array = $this->mask(
                        $array,
                        $path,
                        $this->maskProvider->getMask($maskDefinition->getName()),
                        $maskDefinition->getOptions()
                    );
            }
            unset($array['_id']);
            $masked[] = $this->convertDatesToUTCDateTime($array);
        }
        $targetCollection->insertMany($masked);
    }

    /**
     * @param array $document
     * @param string $path
     * @param MaskStrategyInterface $mask
     * @param array $options
     * @return array
     * @throws InvalidJsonException
     */
    private function mask(array $document, string $path, MaskStrategyInterface $mask, $options = []): array
    {
        $rootObject = new JsonObject($document, true);

        $objs = $rootObject->getJsonObjects($path);
        if (!is_array($objs)) {
            $objs = [$objs];
        }
        foreach ($objs as $obj) {
            if ($obj instanceof JsonObject) {
                $currValue = $obj->get('$');
                $obj->set('$', $mask->apply($currValue, $options));
            } else {
                echo($path ."not found\n");
            }
        }

        return $rootObject->getValue();
    }

    private function convertToArray(iterable $node)
    {
        $result = [];
        foreach ($node as $key => $value) {
            if ($value instanceof BSONDocument || $value instanceof BSONArray) {
                $result[$key] = $this->convertToArray($value);
            } elseif ($value instanceof UTCDateTime) {
                $result[$key] = $value->toDateTime();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function convertDatesToUTCDateTime(array $array): array
    {
        $result = [];
        foreach($array as $key => $item) {
            if (is_array($item)) {
                $result[$key] = $this->convertDatesToUTCDateTime($item);
            } elseif ($item instanceof \DateTime) {
                $result[$key] = new UTCDateTime($item->getTimestamp()* 1000);
            } else {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
