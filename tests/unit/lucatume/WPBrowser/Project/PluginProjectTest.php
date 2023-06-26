<?php


namespace lucatume\WPBrowser\Project;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\CliCommandTestingTools;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class PluginProjectTest extends Unit
{
    use TmpFilesCleanup;
    use UopzFunctions;
    use CliCommandTestingTools;
    use SnapshotAssertions;

    /**
     * It should throw if built on non existing directory
     *
     * @test
     */
    public function should_throw_if_built_on_non_existing_directory(): void
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(PluginProject::ERR_PLUGIN_NOT_FOUND);

        new PluginProject($input, $output, __DIR__ . '/not-a-dir');
    }

    /**
     * It should throw if directory found but not a plugin
     *
     * @test
     */
    public function should_throw_if_directory_found_but_not_a_plugin(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', []);
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(PluginProject::ERR_PLUGIN_NOT_FOUND);

        new PluginProject($input, $output, $pluginDir);
    }

    /**
     * It should build on plugin directory correctly
     *
     * @test
     */
    public function should_build_on_plugin_directory_correctly(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', [
            'plugin.php' => '<?php /* Plugin Name: Acme Plugin */',
        ]);
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $pluginProject = new PluginProject($input, $output, $pluginDir);
        $this->assertEquals('Acme Plugin', $pluginProject->getName());
        $this->assertEquals($pluginDir . '/plugin.php', $pluginProject->getPluginFilePathName());
    }

    /**
     * It should allow exit if SQLite extensions are not found
     *
     * @test
     */
    public function should_allow_exit_sqlite_extensions_not_found(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', [
            'plugin.php' => '<?php /* Plugin Name: Acme Plugin */',
        ]);
        $this->uopzSetFunctionReturn('extension_loaded', function (string $extension): bool {
            if ($extension === 'sqlite3' || $extension === 'pdo_sqlite') {
                return false;
            }
            return extension_loaded($extension);
        }, true);

        $input = $this->buildInteractiveInput([
            'yes' // Confirm exit to install SQLite extensions.
        ]);
        $output = new BufferedOutput();

        $pluginProject = new PluginProject($input, $output, $pluginDir);

        $this->assertNull($pluginProject->setup());
        $this->assertMatchesStringSnapshot($output->fetch());
    }

    /**
     * It should not offer db choice if SQLite not found and continue
     *
     * @test
     */
    public function should_not_offer_db_choice_if_sq_lite_not_found_and_continue(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', [
            'plugin.php' => '<?php /* Plugin Name: Acme Plugin */',
        ]);
        $this->uopzSetFunctionReturn('extension_loaded', function (string $extension): bool {
            if ($extension === 'sqlite3' || $extension === 'pdo_sqlite') {
                return false;
            }
            return extension_loaded($extension);
        }, true);

        $input = $this->buildInteractiveInput([
            'n' // Do not exit to install SQLite extensions.
        ]);
        $output = new BufferedOutput();

        $pluginProject = new PluginProject($input, $output, $pluginDir);

        $this->assertNull($pluginProject->setup());
        $this->assertMatchesStringSnapshot($output->fetch());
    }
}
