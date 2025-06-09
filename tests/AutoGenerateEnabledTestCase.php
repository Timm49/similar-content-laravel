<?php

namespace Timm49\SimilarContentLaravel\Tests;

class AutoGenerateEnabledTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('similar_content.auto_generate', true);
    }
} 