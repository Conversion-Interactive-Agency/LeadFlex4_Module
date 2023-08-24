<?php
namespace conversionia\leadflex\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
class ControlPanel extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@conversionia/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'batchSize.js',
        ];

        parent::init();
    }
}
