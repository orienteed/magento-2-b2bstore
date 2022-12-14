<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver\Mutation;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Api\SaveCartRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class SaveCart implements ResolverInterface
{
    /**
     * @var SaveCartRepositoryInterface
     */
    private $saveCartRepository;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var \Magento\Quote\Model\MaskedQuoteIdToQuoteId
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * SaveCart constructor.
     *
     * @param Data $helperData
     * @param GetCustomer $getCustomer
     * @param SaveCartRepositoryInterface $saveCartRepository
     */
    public function __construct(
        GetCustomer $getCustomer,
        SaveCartRepositoryInterface $saveCartRepository,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
    ) {
        $this->saveCartRepository = $saveCartRepository;
        $this->getCustomer = $getCustomer;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['cart_id'])) {
            throw new GraphQlInputException(__('"cart_id" value should be specified'));
        }
        if (!isset($args['cart_name'])) {
            throw new GraphQlInputException(__('"cart_name" value should be specified'));
        }
        $customer = $this->getCustomer->execute($context);

        $cartId = $this->maskedQuoteIdToQuoteId->execute($args['cart_id']);

        try {
            return $this->saveCartRepository->save((int)$customer->getId(), $cartId, $args['cart_name'], $args['description']);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlAlreadyExistsException(__($e->getMessage()));
        }
    }
}
