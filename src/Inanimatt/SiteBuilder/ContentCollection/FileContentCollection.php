<?php
namespace Inanimatt\SiteBuilder\ContentCollection;
use Symfony\Component\Finder\Finder;
use Inanimatt\SiteBuilder\ContentObject\PhpFileContentObject;
use Inanimatt\SiteBuilder\ContentObject\MarkdownFileContentObject;

class FileContentCollection implements ContentCollectionInterface
{
    protected $path;
    protected $finder;
    protected $collection;

    public function __construct($path = null)
    {
        $this->finder = new Finder;
        
        if ($path) {
            $this->setPath($path);
        }
    }
  
    public function setPath($path)
    {
        $this->path = $path;
    }
  
    public function getObjects()
    {
        $files = array();
        
        $this->finder->files()
            ->in($this->path)
            ->name('*.php')
            ->name('*.md')
        ;
        
        foreach($this->finder as $file) {
            // FIXME: Abstract this to a factory with registered handlers
            if ($file->getExtension() == 'php') {
                $files[] = new PhpFileContentObject($file, $file->getRelativePath(), $file->getRelativePathName());
            }
            
            if ($file->getExtension() == 'md') {
                $files[] = new MarkdownFileContentObject($file, $file->getRelativePath(), $file->getRelativePathName());
            }
        }
            
        return $files;

    }

}