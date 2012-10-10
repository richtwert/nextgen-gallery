<?php

class C_Cache extends C_MVC_Controller
{
	function define($context = FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Cache');
	}
}

class Mixin_Cache extends Mixin
{
    /**
     * Empties a directory of all of its content
     *
     * @param string $directory Absolute path
     * @param bool $recursive Remove files from subdirectories of the cache
     * @param string $regex (optional) Only remove files matching pattern; '/^.+\.png$/i' will match all .png
     */
    public function flush_directory($directory, $recursive = TRUE, $regex = NULL)
    {
        if ($recursive)
        {
            $directory = new DirectoryIterator($directory);
        }
        else {
            $directory = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::CHILD_FIRST
            );
        }

        if (!is_null($regex))
        {
            $iterator = RegexIterator($directory, $regex, RecursiveRegexIterator::GET_MATCH);
        }
        else {
            $iterator = $directory;
        }

        foreach ($iterator as $file) {
            if ($file->isFile() || $file->isLink()) {
                unlink($file->getPathname());
            }
            elseif ($file->isDir() && !$file->isDot() && $recursive) {
                rmdir($file->getPathname());
            }
        }
    }

}
