<?php

use App\Config\ConfigLoader;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;

class ConfigFileContext implements Context
{
    /**
     * @Given the file :filename doesn't exist
     * @param string $filename
     */
    public function theFileDoesntExist(string $filename)
    {
        if (file_exists(ConfigLoader::forgePath($filename))) {
            unlink(ConfigLoader::forgePath($filename));
        }
    }

    /**
     * @Then YAML file :filename should be like:
     * @param string $filename
     * @param PyStringNode $string
     * @throws Exception
     */
    public function YamlfileShouldBeLike(string $filename, PyStringNode $string)
    {
        if (Yaml::parse(file_get_contents(ConfigLoader::forgePath($filename))) != Yaml::parse($string->getRaw())) {
            throw new \Exception('File content does not match the template');
        }
    }

    /**
     * @Given the config :filename contains:
     */
    public function theConfigTestYamlContains(string $filename, PyStringNode $string)
    {
        file_put_contents(ConfigLoader::forgePath($filename), $string->getRaw());
    }
}
