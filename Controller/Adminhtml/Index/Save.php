<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AHT\GssiCustomily\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
/*use AHT\Portfolio\Api\CategoryRepositoryInterface;*/
use AHT\GssiCustomily\Model\GssiCustomily;
use AHT\GssiCustomily\Model\GssiCustomilyFactory;
use AHT\GssiCustomily\Model\ResourceModel\GssiCustomily as Resource;

/**
 * Save CMS block action.
 */
class Save extends \AHT\GssiCustomily\Controller\Adminhtml\GssiCustomily implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    protected $resource;

    /**

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param BlockFactory|null $blockFactory
     * @param BlockRepositoryInterface|null $blockRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        GssiCustomilyFactory $blockFactory = null,
        Resource $resource
        /*CategoryRepositoryInterface $blockRepository = null*/
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->blockFactory = $blockFactory
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(GssiCustomilyFactory::class);
        /*$this->blockRepository = $blockRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);*/
        parent::__construct($context, $coreRegistry);
        $this->resource = $resource;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /*if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Block::STATUS_ENABLED;
            }*/

            if (empty($data['id'])) {
                $data['id'] = null;
            }
            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->blockFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                try {
                    /*$model = $this->blockRepository->getById($id);*/
                    $this->resource->load($model, $id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);
            try {
               /* $this->blockRepository->save($model);*/

                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the block.'));
                $this->dataPersistor->clear('gssicustomily');
                return $this->processBlockReturn($model, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('gssicustomily', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process and set the block return
     *
     * @param \Magento\Cms\Model\Block $model
     * @param array $data
     * @param \Magento\Framework\Controller\ResultInterface $resultRedirect
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processBlockReturn($model, $data, $resultRedirect)
    {
        $redirect = $data['back'] ?? 'close';

        if ($redirect ==='continue') {
            $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
        } else if ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        } else if ($redirect === 'duplicate') {
            $duplicateModel = $this->blockFactory->create(['data' => $data]);
            $duplicateModel->setId(null);
            $duplicateModel->setIdentifier($data['identifier'] . '-' . uniqid());
            $duplicateModel->setIsActive(Block::STATUS_DISABLED);
            $this->blockRepository->save($duplicateModel);
            $id = $duplicateModel->getId();
            $this->messageManager->addSuccessMessage(__('You duplicated the block.'));
            $this->dataPersistor->set('gssicustomily', $data);
            $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect;
    }
}
