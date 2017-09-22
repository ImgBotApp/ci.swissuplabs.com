<?php
namespace App\Tests;

use App\Lib\Terminal;

class Phpunit extends Test
{
    public function getTitle()
    {
        return 'PHP Unit';
    }

    /**
     * Run the test and return console output.
     * If the test was successfull, result will be an empty string.
     *
     * @return string
     */
    public function run()
    {
        $path = $this->getPath();
        // $path = realpath($this->getPath() . '/../ajaxsearch');
        $pathUnitTest = $path . '/Test/Unit';
        if (!file_exists($pathUnitTest)) {
            return '';
        }

        $bootstrap = storage_path('app/tools/m2/dev/tests/unit/framework/bootstrap.php');
        $customBootstrap = str_replace('bootstrap.php', 'bootstrap.' . md5($path) . '.php', $bootstrap);

        if (!file_exists($customBootstrap)) {
            $composer = file_get_contents($path . '/composer.json');
            $composer = json_decode($composer);

            $namespace = (array) $composer->autoload;
            $namespace = (array) $namespace['psr-4'];
            $namespace = array_keys($namespace);
            $namespace = array_pop($namespace);
            $namespace = str_replace("\\", "\\\\", $namespace);

            $code = '$classLoader = new \Composer\Autoload\ClassLoader();' . "\n" .
                '$classLoader->addPsr4("' . $namespace . '", \'' . $path . '\', true);' . "\n" .
                '$classLoader->register();';
            $content = file_get_contents($bootstrap);
            file_put_contents($customBootstrap, $content . "\n" . $code);
        }
        $command = implode(' && ', [
            sprintf(
                "%s --bootstrap %s %s",
                storage_path('app/tools/phpunit'),
                $customBootstrap,
                escapeshellarg($path)
            )
        ]);

        $output = Terminal::exec($command);
        $output = str_replace(storage_path(), '', $output);

        $fingerprint = 'FAILURES!';
        return false === strstr($output, $fingerprint) ? '' : $output;
    }
}
