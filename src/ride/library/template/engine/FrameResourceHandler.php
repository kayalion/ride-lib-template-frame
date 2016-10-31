<?php

namespace ride\library\template\engine;

use frame\library\resource\AbstractTemplateResourceHandler;

use ride\library\system\file\browser\FileBrowser;
use ride\library\template\exception\ResourceNotFoundException;

class FrameResourceHandler extends AbstractTemplateResourceHandler {

    /**
     * File browser to lookup the templates
     * @var \ride\library\system\file\browser\FileBrowser;
     */
    protected $fileBrowser;

    /**
     * Path for the file browser
     * @var string
     */
    protected $path;

    /**
     * Themes to use when looking for resources
     * @var array
     */
    protected $themes;

    /**
     * Id of the template
     * @var string
     */
    protected $templateId;

    /**
     * Constructs a new resource handler
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser File
     * browser to lookup the templates
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, $path = null) {
        $this->fileBrowser = $fileBrowser;

        $this->setPath($path);
    }

    /**
     * Sets the path for the file browser
     * @param string $path
     * @return null
     * @throws \ride\library\template\exception\TemplateException when the
     * provided path is invalid or empty
     */
    public function setPath($path) {
        if ($path !== null && (!is_string($path) || !$path)) {
            throw new TemplateException('Could not set the path for the file browser: provided path is empty or invalid');
        }

        $this->path = $path;
    }

    /**
     * Sets the themes used for looking the template resource
     * @param array $themes Array with the name of the themes as key
     * @return null
     * @throws \ride\library\template\exception\TemplateException when the
     * provided theme is invalid or empty
     */
    public function setThemes(array $themes = null) {
        $this->themes = $themes;
    }

    /**
     * Sets the id of the template. Don't forget to set compile_id on the
     * Smarty engine itself.
     * @param string $templateId Id of the template
     * @return null
     */
    public function setTemplateId($templateId) {
        $this->templateId = $templateId;
    }

    /**
     * Gets a resource by the provided name
     * @param string $name Name of a template resource. This is the name which
     * is passed as resource to the template engine and used in extends and
     * include blocks
     * @return string Contents of the template.
     * @throws \frame\library\exception\NotFoundTemplateException when the
     * resource does not exist
     */
    public function getResource($name) {
        $templateFile = $this->getFile($name);

        $resource = $templateFile->read();

        $this->requestedResources[$name] = true;

        return $resource;
    }

    /**
     * Gets the modification time of the provided resource
     * @param string $name Name of the template resource as used in getResource
     * @return integer|null Timestamp of the modification time or null when
     * unknown
     * @throws \frame\library\exception\NotFoundTemplateException when the
     * resource does not exist
     * @see getResource
     */
    public function getModificationTime($name) {
        $templateFile = $this->getFile($name);

        return $templateFile->getModificationTime();
    }

    /**
     * Get the source file of a template
     * @param string $name Relative path of the template to the view folder
     * without the extension
     * @return \ride\library\system\file\File instance of a File if the source
     * is found, null otherwise
     */
    public function getFile($name) {
        $file = null;

        if ($this->themes) {
            foreach ($this->themes as $theme => $null) {
                try {
                    $file = $this->getThemeFile($name, $theme);

                    break;
                } catch (ResourceNotFoundException $exception) {
                    $file = null;
                }
            }
        }

        if (!$file) {
            $file = $this->getThemeFile($name);
        }

        return $file;
    }

    /**
     * Gets the source file of a template
     * @param string $name Relative path of the template to the view folder
     * without the extension
     * @return \ride\library\system\file\File Instance of a File if the source
     * is found, null otherwise
     */
    protected function getThemeFile($name, $theme = null) {
        $path = '';
        if ($theme) {
            $path = $theme . '/';
        }

        if ($this->path) {
            $path = $this->path . '/' . $path;
        }

        if ($path) {
            $path = rtrim($path, '/') . '/';
        }

        $file = null;

        if ($this->templateId) {
            $fileName = $path . $name . '.' . $this->templateId . '.' . FrameEngine::EXTENSION;

            $file = $this->fileBrowser->getFile($fileName);
        }

        if (!$file) {
            $fileName = $path . $name . '.' . FrameEngine::EXTENSION;

            $file = $this->fileBrowser->getFile($fileName);
        }

        if (!$file) {
            throw new ResourceNotFoundException($fileName);
        }

        return $file;
    }

    /**
     * Gets the available template resources for the provided namespace
     * @param string $namespace
     * @return array
     */
    public function getFiles($namespace) {
        $files = array();

        $basePath = '';
        if ($this->path) {
            $basePath = $this->path . '/';
        }
        if ($this->themes) {
            foreach ($this->themes as $theme => $null) {
                $path = $basePath . $theme . '/' . $namespace;

                $files += $this->getPathFiles($path, $basePath . $theme . '/');
            }
        } else {
            $path = $basePath . $namespace;

            $files += $this->getPathFiles($path, $basePath);
        }

        return $files;
    }

    /**
     * Gets the files for the provided path
     * @param string $path Relative path in the Ride file structure of the
     * requested files
     * @param string $basePath Relative path in the Ride file structure of the
     * engine and theme
     * @return array
     */
    protected function getPathFiles($path, $basePath) {
        $files = array();

        $pathDirectories = $this->fileBrowser->getFiles($path);
        if (!$pathDirectories) {
            return $files;
        }

        $path = rtrim($path, '/') . '/';

        foreach ($pathDirectories as $pathDirectory) {
            $pathFiles = $pathDirectory->read();
            foreach ($pathFiles as $pathFile) {
                if ($pathFile->isDirectory() || $pathFile->getExtension() != FrameEngine::EXTENSION) {
                    continue;
                }

                $pathFile = $this->fileBrowser->getRelativeFile($pathFile);
                $filePath = $pathFile->getPath();

                $resultPath = substr(str_replace($basePath, '', $filePath), 0, (strlen(FrameEngine::EXTENSION) + 1) * -1);
                $resultName = substr(str_replace($path, '', $filePath), 0, (strlen(FrameEngine::EXTENSION) + 1) * -1);

                $files[$resultPath] = $resultName;
            }
        }

        return $files;
    }

}
