<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\Cart\Controller;

use Pyz\Yves\Cart\Plugin\Provider\CartControllerProvider;
use Spryker\Yves\Kernel\Controller\AbstractController;

/**
 * @method \Spryker\Client\Cart\CartClientInterface getClient()
 * @method \Pyz\Yves\Cart\CartFactory getFactory()
 */
class CartController extends AbstractController
{

    /**
     * @param array|null $selectedAttributes
     *
     * @return array
     */
    public function indexAction($selectedAttributes = null)
    {
        $quoteTransfer = $this->getClient()
            ->getQuote();

        $voucherForm = $this->getFactory()
            ->createVoucherForm();

        $cartItems = $this->getFactory()
            ->createProductBundleGrouper()
            ->getGroupedBundleItems($quoteTransfer->getItems(), $quoteTransfer->getBundleItems());

        $stepBreadcrumbsTransfer = $this->getFactory()
            ->getCheckoutBreadcrumbPlugin()
            ->generateStepBreadcrumbs($quoteTransfer);

        $itemAttributesBySku = $this->getFactory()
            ->createCartItemsAttributeProvider()->getItemsAttributes($quoteTransfer, $selectedAttributes);

        return $this->viewResponse([
            'cart' => $quoteTransfer,
            'cartItems' => $cartItems,
            'attributes' => $itemAttributesBySku,
            'voucherForm' => $voucherForm->createView(),
            'stepBreadcrumbs' => $stepBreadcrumbsTransfer,
        ]);
    }

    /**
     * @param string $sku
     * @param int $quantity
     * @param array $optionValueIds
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction($sku, $quantity, $optionValueIds = [])
    {
        $cartOperationHandler = $this->getCartOperationHandler();
        $cartOperationHandler->add($sku, $quantity, $optionValueIds);
        $cartOperationHandler->setFlashMessagesFromLastZedRequest($this->getClient());

        return $this->redirectResponseInternal(CartControllerProvider::ROUTE_CART);
    }

    /**
     * @param string $sku
     * @param string|null $groupKey
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($sku, $groupKey = null)
    {
        $cartOperationHandler = $this->getCartOperationHandler();
        $cartOperationHandler->remove($sku, $groupKey);
        $cartOperationHandler->setFlashMessagesFromLastZedRequest($this->getClient());

        return $this->redirectResponseInternal(CartControllerProvider::ROUTE_CART);
    }

    /**
     * @param string $sku
     * @param int $quantity
     * @param string|null $groupKey
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeAction($sku, $quantity, $groupKey = null)
    {
        $cartOperationHandler = $this->getCartOperationHandler();
        $cartOperationHandler->changeQuantity($sku, $quantity, $groupKey);
        $cartOperationHandler->setFlashMessagesFromLastZedRequest($this->getClient());

        return $this->redirectResponseInternal(CartControllerProvider::ROUTE_CART);
    }

    /**
     * @param string $sku
     * @param int $quantity
     * @param array $selectedAttributes
     * @param array $preselectedAttributes
     * @param string|null $groupKey
     * @param array $optionValueIds
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction($sku, $quantity, $selectedAttributes, $preselectedAttributes,  $groupKey = null, $optionValueIds = [])
    {

        $quoteTransfer = $this->getClient()->getQuote();

        $isItemReplacedInCart = $this->getFactory()->createCartItemsAttributeProvider()
            ->tryToReplaceItem(
                $sku,
                $quantity,
                array_replace($selectedAttributes, $preselectedAttributes),
                $quoteTransfer->getItems(),
                $groupKey,
                $optionValueIds
            );

        if ($isItemReplacedInCart) {
            return $this->redirectResponseInternal(CartControllerProvider::ROUTE_CART);
        }

        return $this->redirectResponseInternal(
            CartControllerProvider::ROUTE_CART,
            $this->getFactory()
                ->createCartItemsAttributeProvider()->formatUpdateActionResponse($sku, $selectedAttributes)
        );
    }

    /**
     * @return \Pyz\Yves\Cart\Handler\ProductBundleCartOperationHandler
     */
    protected function getCartOperationHandler()
    {
        return $this->getFactory()->createProductBundleCartOperationHandler();
    }

}
