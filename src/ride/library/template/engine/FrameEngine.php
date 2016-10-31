<?php

namespace ride\library\template\engine;

use frame\library\TemplateContext;
use frame\library\TemplateEngine;

use ride\library\system\file\File;
use ride\library\template\exception\ResourceNotSetException;
use ride\library\template\exception\TemplateException;
use ride\library\template\Template;
use ride\library\template\ThemedTemplate;

use \Exception;

/**
 * Implementation of the Frmae template engine
 */
class FrameEngine extends AbstractEngine {

    /**
     * Name of this engine
     * @var string
     */
    const NAME = 'frame';

    /**
     * Extension for the template resources
     * @var string
     */
    const EXTENSION = 'tpl';

    /**
     * Tag to open a block comment
     * @var string
     */
    const COMMENT_OPEN = '{*';

    /**
     * Tag to close a block comment
     * @var string
     */
    const COMMENT_CLOSE = '*}';

    /**
     * Instance of the Frame engine
     * @var
     */
    protected $frame;

    /**
     * Implementation of the resource handler
     * @var \ride\library\template\FrameResourceHandler
     */
    protected $resourceHandler;

    /**
     * Constructs a new Frame template engine
     * @param \frame\library\TemplateContext $context Initial context for the
     * template engine
     * @param \frame\library\TemplateEngine $frame Instance of the template
     * engine
     * @return null
     */
    public function __construct(TemplateEngine $frame) {
        $this->resourceHandler = $frame->getContext()->getResourceHandler();
        $this->frame = $frame;
    }

    /**
     * Renders a template
     * @param \ride\library\template\Template $template Template to render
     * @return string Rendered template
     * @throws \ride\library\template\exception\ResourceNotSetException when
     * no template resource was set to the template
     * @throws \ride\library\template\exception\ResourceNotFoundException when
     * the template resource could not be found by the engine
     */
    public function render(Template $template) {
        $resource = $template->getResource();
        if (!$resource) {
            throw new ResourceNotSetException();
        }

        $this->preProcess($template);

        try {
            $output = $this->frame->render($resource, $template->getVariables());

            $exception = null;
        } catch (Exception $exception) {
            $exception = new TemplateException('Could not render ' . $resource, 0, $exception);
        }

        $this->postProcess();

        if ($exception) {
            throw $exception;
        }

        return $output;
    }

    /**
     * Gets the template resource
     * @param \ride\library\template\Template $template Template to get the
     * resource of
     * @return \ride\library\system\file\File $file File instance for the
     * template resource
     * @throws \ride\library\template\exception\ResourceNotSetException when
     * no template was set to the template
     * @throws \ride\library\template\exception\ResourceNotFoundException when
     * the template could not be found by the engine
     */
    public function getFile(Template $template) {
        $resource = $template->getResource();
        if (!$resource) {
            throw new ResourceNotSetException();
        }

        $this->preProcess($template);

        return $this->resourceHandler->getFile($resource);
    }

    /**
     * Gets the available template resources for the provided namespace
     * @param string $namespace
     * @param string $theme
     * @return array Array with the relative path of the resource as key and the
     * name as value
     */
    public function getFiles($namespace, $theme = null) {
        $theme = $this->themeModel->getTheme($theme);
        $themeHierarchy = $this->getThemeHierarchy($theme);

        $this->resourceHandler->setThemes($themeHierarchy);

        $files = $this->resourceHandler->getFiles($namespace);

        $this->postProcess();

        return $files;
    }

    /**
     * Preprocess this engine before performing a template action
     * @param \ride\library\template\Template $template
     * @return null
     */
    protected function preProcess(Template $template) {
        if (!$template instanceof ThemedTemplate) {
            return;
        }

        $themeHierarchy = $this->getTheme($template);

        $this->resourceHandler->setThemes($themeHierarchy);

        $this->frame->setCompileId($template->getTheme());

        $templateId = $template->getResourceId();
        if ($templateId) {
            $this->resourceHandler->setTemplateId($templateId);
            $this->frame->setCompileId($this->frame->getCompileId() . '-' . $templateId);
        }
    }

    /**
     * Postprocess this engine after performing a template action
     * @return null
     */
    protected function postProcess() {
        $this->resourceHandler->setThemes(null);
        $this->resourceHandler->setTemplateId(null);
        $this->frame->setCompileId(null);
    }

}
