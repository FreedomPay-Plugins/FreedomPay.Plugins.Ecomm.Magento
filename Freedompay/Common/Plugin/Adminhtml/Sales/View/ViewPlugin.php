<?php
namespace Freedompay\Common\Plugin\Adminhtml\Sales\View;

use Magento\Sales\Block\Adminhtml\Order\View;
use Freedompay\Common\Block\Adminhtml\Order\View\Info\AuthorizationExpiryMessage;

/**
 * Before plugin to alter the style of invoice button in backend order page
 */
class ViewPlugin
{
    /**
     * @var AuthorizationExpiryMessage
     */
    private AuthorizationExpiryMessage $authorizationMessage;

    /**
     * @param AuthorizationExpiryMessage $authorizationMessage
     */
    public function __construct(
        AuthorizationExpiryMessage $authorizationMessage
    ) {
        $this->authorizationMessage = $authorizationMessage;
    }

    /**
     * Before plugin function
     *
     * @param View $subject
     * @param string $buttonId
     * @param array<mixed> $data
     * @return mixed
     */
    public function beforeAddButton(View $subject, $buttonId, $data)
    {
        if ($buttonId === 'order_invoice') {
            if ($this->authorizationMessage->getAuthorizationExpiryStatus()) {
                $data['title'] =  __('Invoice-Authorization would expire soon!');
                $data['style'] =  'color: red';
                return [$buttonId, $data];
            }
        }
        return  null;
    }
}
