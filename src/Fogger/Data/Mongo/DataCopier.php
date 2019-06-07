<?php

namespace App\Fogger\Data\Mongo;

use App\Fogger\Mask\Exception\UnknownMaskException;
use App\Fogger\Mask\MaskStrategyInterface;
use App\Fogger\Mask\MaskStrategyProvider;
use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
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

    public function copyDataChunk(ChunkMessage $message)
    {
        $source = $message->getSource();
        $target = $message->getTarget();
        $collectionName = $message->getCollection()->getName();

        $sourceCollection = $this->client->$source->$collectionName;
        $targetCollection = $this->client->$target->$collectionName;
        $cursor = $sourceCollection->find(
            [
                '_id' => [
                    '$in' => array_map(
                        function ($item) {
                            return new ObjectId($item);
                        },
                        $message->getKeys()
                    ),
                ],
            ]
        );
        $masked = [];

        /** @var BSONDocument $document */
        foreach ($cursor as $document) {
            $array = json_decode(json_encode($document), true);
            foreach ($message->getCollection()->getMasks() as $path => $maskDefinition) {
                try {
                    $array = $this->mask(
                        $array,
                        $path,
                        $this->maskProvider->getMask($maskDefinition->getName()),
                        $maskDefinition->getOptions()
                    );
                } catch (UnknownMaskException $e) {
                }
            }
            unset($array['_id']);
            $masked[] = $array;
        }
        $targetCollection->insertMany($masked);
    }

    private function mask(array $document, string $path, MaskStrategyInterface $mask, $options = []): array
    {
        try {
            $rootObject = new JsonObject($document, true);
        } catch (InvalidJsonException $e) {
        }

        $objs = $rootObject->getJsonObjects($path);
        if (!is_array($objs)) {
            $objs = [$objs];
        }
        foreach ($objs as $obj) {
            $currValue = $obj->get('$');
            $obj->set('$', $mask->apply($currValue, $options));
        }

        return $rootObject->getValue();
    }
}
