<?php

$sc = require_once __DIR__.'/src/bootstrap.php';

// Render the site with the given config file

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$console = new Application('SiteBuilder', '2.0-dev');

$console
    ->register('rebuild')
    ->setDescription('Renders all content, writes to output folder')
    ->setHelp(<<<'EOH'
This command renders your content files and saves them in the output folder.
Run it like this:

        %command.full_name%
EOH
)
    ->setDefinition(array())
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($sc) {
        $contentCollection = $sc->get('contentcollection');
        $builder = $sc->get('sitebuilder');
        $serialiser = $sc->get('serialiser');

        foreach ($contentCollection->getObjects() as $content) {
            $output->writeln(sprintf('Rendering <info>%s</info>', $content->getRelativePathName()));

            $out = $builder->renderFile($content, array(
                'app' => array(
                    'contentcollection' => $contentCollection,
                    'contentobject' => $content,
                ),
            ));

            $serialiser->write($out, $content->getOutputName());
        }
    })
;

$console
    ->register('init')
    ->setDescription('Create necessary folders')
    ->setHelp(<<<'EOH'
This command creates the content, output, and template folders that
SiteBuilder expects.

Run it like this:

        %command.full_name%
EOH
)
    ->setDefinition(array())
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($sc) {
        foreach (array('content', 'output', 'templates') as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        if (!is_file('config.ini')) {
            $template =<<<'EOF'
[parameters]

; Where to look for templates, and the name of the default template
; Use .php to render with the PHP template renderer, .twig for Twig
template_path = templates
default_template = template.twig

; Where to look for content files
content_dir = content

; Where to put the generated site
output_dir = output

EOF;

            file_put_contents('config.ini', $template);
        }

        if (!is_file('templates/template.twig')) {
            $template =<<<'EOF'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>{{ title }}</title>
</head>
<body>
    {{ content | raw }}
</body>
</html>

EOF;

            file_put_contents('templates/template.twig', $template);
        }

        if (!is_file('content/example.md')) {
            $example =<<<'EOF'
---
# This is a YAML block and sets variables you can call in your template, for example:
title: Markdown example

# You can also override the default template by setting a template variable, e.g.:
template: template.twig
---

#Hello World

Lorem ipsum dolor sit *amet*, consectetur adipisicing elit, [sed do eiusmod tempor](http://www.example.com/) incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco **laboris nisi** ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

EOF;
            file_put_contents('content/example.md', $example);
        }

    })
;

$console->run();
