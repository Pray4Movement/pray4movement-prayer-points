<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'pray4movement-prayer-points/pray4movement-prayer-points.php' );

        $this->assertContains(
            'pray4movement-prayer-points/pray4movement-prayer-points.php',
            get_option( 'active_plugins' )
        );
    }
}
