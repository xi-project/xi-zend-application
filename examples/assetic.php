<?php
/**
 * This is an example configuration file for the Assetic application resource
 * intended for consumption by a Bootstrap.
 * 
 * All relative directory references are in relation to APPLICATION_PATH.
 */
return array(
    'resources' => array(
        'assetic' => array(
            // Should we compile assets?
            'enabled' => true,
            'debug' => false,
            'cachePath' => '../data/cache',
            'publicPath' => '../public',
            
            // Declare enabled filters and some file system paths they depend on
            'filters' => array(
                'paths' => array(
                    'java' => "/usr/bin/java",
                    'node' => "/usr/local/bin/node",
                    'optipng' => "/usr/bin/optipng",
                ),
                'enabled' => array(
                    'less',
                    'closure',
                    'optimize-png',
                    'optimize-jpeg'
                )
            ),
            
            // List of parsers predefined for 'directories'
            'parsers' => array(
                'less' => array(
                    // Possible keys: match, filters, output, cache, options
                    'match' => '/\.less$/',
                    'filters' => array('less'),
                    'output' => 'css/*.css',
                    'cache' => true
                ),
                'png' => array(
                    'match' => '/\.png$/',
                    'filters' => array('optimize-png'),
                    'output' => 'img/*.png'
                ),
                'jpeg' => array(
                    'match' => '/\.jpe?g$/',
                    'filters' => array('optimize-jpeg'),
                    'output' => 'img/*.jpeg'
                ),
                'gif' => array(
                    'match' => '/\.gif$/',
                    'filters' => array(),
                    'output' => 'img/*.gif'
                )
            ),
            
            // Whole directories to trawl recursively for resources
            'directories' => array(
                array(
                    // From where to attempt to find resources
                    'path' => 'modules/ExampleModule/Resources/public',
                    // List of regexes based on which to exclude certain files
                    'blacklist' => array(
                        // '/you_do_not_want_to_publish_me/'
                    ),
                    // List of parsers to use for asset creation
                    'parsers' => array('less', 'coffeescript', 'png', 'jpeg', 'gif')
                )
            ),
            
            // Single files
            'files' => array(
                'css' => array(
                    // Possible keys: inputs, filters, output, cache, options
                    'inputs' => array(
                        'assets/lol.less'
                    ),
                    'filters' => array('less'),
                    'output' => 'css/*.css'
                )
            )
        )
    )
);