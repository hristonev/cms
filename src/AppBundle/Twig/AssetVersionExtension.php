<?php

namespace AppBundle\Twig;

use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AssetVersionExtension extends \Twig_Extension
{
    private $appDir;

    public function __construct($appDir)
    {
        $this->appDir = $appDir;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('asset_version', [$this, 'getAssetVersion'])
        ];
    }

    public function getAssetVersion($filename)
    {
        $manifestPath = $this->appDir. '/Resources/assets/rev-manifest.json';
        if(!is_file($manifestPath)) {
            throw new NotFoundResourceException(sprintf('Missing manifest file %s', $manifestPath));
        }

        $assets = json_decode(file_get_contents($manifestPath));
        if(!isset($assets->$filename)){
            throw new NotFoundResourceException(sprintf('Missing asset version path for %s', $filename));
        }

        return $assets->$filename;
    }

    public function getName()
    {
        return 'asset_version';
    }
}