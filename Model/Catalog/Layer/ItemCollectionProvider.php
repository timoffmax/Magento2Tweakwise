<?php
/**
 * Tweakwise (https://www.tweakwise.com/) - All Rights Reserved
 *
 * @copyright Copyright (c) 2017-2022 Tweakwise.com B.V. (https://www.tweakwise.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Tweakwise\Magento2Tweakwise\Model\Catalog\Layer;

use Tweakwise\Magento2Tweakwise\Exception\TweakwiseException;
use Tweakwise\Magento2Tweakwise\Model\Catalog\Product\CollectionFactory;
use Tweakwise\Magento2Tweakwise\Model\Config;
use Tweakwise\Magento2TweakwiseExport\Model\Logger;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;

class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemCollectionProviderInterface
     */
    protected $originalProvider;

    /**
     * @var NavigationContext
     */
    protected $navigationContext;

    /**
     * Proxy constructor.
     *
     * @param Config $config
     * @param Logger $log
     * @param ItemCollectionProviderInterface $originalProvider
     * @param CollectionFactory $collectionFactory
     * @param NavigationContext $navigationContext
     */
    public function __construct(
        Config $config,
        Logger $log,
        ItemCollectionProviderInterface $originalProvider,
        CollectionFactory $collectionFactory,
        NavigationContext $navigationContext
    ) {
        $this->config = $config;
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->originalProvider = $originalProvider;
        $this->navigationContext = $navigationContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(Category $category)
    {
        if (!$this->config->isLayeredEnabled()) {
            return $this->originalProvider->getCollection($category);
        }

        try {
            $collection = $this->collectionFactory->create(['navigationContext' => $this->navigationContext]);

            if (count($collection->getItems()) < 1) {
                $collection = $this->collectionFactory
                    ->create(['navigationContext' => $this->navigationContext->resetPagination()])
                ;
            }

            $collection->clear();

            return $collection;
        } catch (TweakwiseException $e) {
            $this->log->critical($e);
            $this->config->setTweakwiseExceptionThrown();

            return $this->originalProvider->getCollection($category);
        }
    }
}
