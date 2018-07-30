<?php

namespace Adshares\AdsOperator\Document\Transaction;

use Adshares\Ads\Entity\Transaction\SendManyTransaction as BaseSendManyTransaction;
use Adshares\Ads\Entity\Transaction\SendManyTransactionWire;
use Adshares\AdsOperator\Document\ArrayableInterface;

/**
 * Class SendManyTransaction
 * @package Adshares\AdsOperator\Document\Transaction
 */
class SendManyTransaction extends BaseSendManyTransaction implements ArrayableInterface
{
    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'size' => $this->size,
            'type' => $this->type,
            'blockId' => $this->blockId,
            'messageId' => $this->messageId,
            'msgId' => $this->msgId,
            'node' => $this->node,
            'senderAddress' => $this->senderAddress,
            'senderFee' => $this->senderFee,
            'signature' => $this->signature,
            'wireCount' => $this->wireCount,
            'time' => $this->time,
            'user' => $this->user,
            'wires' => $this->transformTransactionWiresToArray($this->wires),
        ];
    }

    /**
     * @param array $wires
     * @return array
     */
    private function transformTransactionWiresToArray(array $wires): array
    {
        $transformed = [];

        /** @var SendManyTransactionWire $transaction */
        foreach ($wires as $transaction) {
            $transformed[] = [
                'amount' => $transaction->getAmount(),
                'targetAddress' => $transaction->getTargetAddress(),
                'targetNode' => $transaction->getTargetNode(),
                'targetUser' => $transaction->getTargetUser(),
            ];
        }

        return $transformed;
    }
}