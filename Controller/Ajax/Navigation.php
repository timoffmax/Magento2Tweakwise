<?php

namespace Tweakwise\Magento2Tweakwise\Controller\Ajax;

use Tweakwise\Magento2Tweakwise\Model\AjaxNavigationResult;
use Tweakwise\Magento2Tweakwise\Model\AjaxResultInitializer\InitializerInterface;
use Tweakwise\Magento2Tweakwise\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Navigation
 * Handles ajax filtering requests for category pages
 * @package Tweakwise\Magento2Tweakwise\Controller\Ajax
 */
class Navigation extends Action
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AjaxNavigationResult
     */
    protected $ajaxNavigationResult;

    /**
     * @var InitializerInterface[]
     */
    protected $initializerMap;

    /**
     * Navigation constructor.
     * @param Context $context Request context
     * @param Config $config Tweakwise configuration provider
     * @param AjaxNavigationResult $ajaxNavigationResult
     * @param array $initializerMap
     */
    public function __construct(
        Context $context,
        Config $config,
        AjaxNavigationResult $ajaxNavigationResult,
        array $initializerMap
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->ajaxNavigationResult = $ajaxNavigationResult;
        $this->initializerMap = $initializerMap;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->config->isAjaxFilters()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $request = $this->getRequest();
        $type = $request->getParam('__tw_ajax_type');
        if (!isset($this->initializerMap[$type])) {
            throw new \InvalidArgumentException('No ajax navigation result handler found for ' . $type);
        }

        $this->initializerMap[$type]->initializeAjaxResult(
            $this->ajaxNavigationResult,
            $request
        );

        return $this->ajaxNavigationResult;
    }
}
