<?php
/**
 * Copyright (C) 2018 Adshares sp. z o.o.
 *
 * This file is part of ADS Operator
 *
 * ADS Operator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ADS Operator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ADS Operator.  If not, see <https://www.gnu.org/licenses/>
 */

namespace Adshares\AdsOperator\Controller\Blockexplorer;

use Adshares\AdsOperator\Controller\ApiController;
use Adshares\AdsOperator\Document\Block;
use Adshares\AdsOperator\Document\Message;
use Adshares\AdsOperator\Document\Transaction;
use Adshares\AdsOperator\Repository\BlockRepositoryInterface;
use Adshares\AdsOperator\Repository\MessageRepositoryInterface;
use Adshares\AdsOperator\Repository\TransactionRepositoryInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BlockController extends ApiController
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * BlockController constructor.
     * @param BlockRepositoryInterface $repository
     * @param MessageRepositoryInterface $messageRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        BlockRepositoryInterface $repository,
        MessageRepositoryInterface $messageRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->repository = $repository;
        $this->messageRepository = $messageRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @Operation(
     *     summary="List of blocks",
     *     tags={"Blockexplorer"},
     *
     *      @SWG\Response(
     *          response=400,
     *          description="Returned when query parameters are invalid"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Returned when operation is successful",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref=@Model(type=Block::class))
     *          )
     *      ),
     *     @SWG\Parameter(
     *          name="sort",
     *          in="query",
     *          type="string",
     *          description="The field used to sort blocks"
     *      ),
     *      @SWG\Parameter(
     *          name="order",
     *          in="query",
     *          type="string",
     *          description="The field used to set ordering for blocks"
     *      ),
     *      @SWG\Parameter(
     *          name="limit",
     *          in="query",
     *          type="integer",
     *          description="The field used to limit number of blocks"
     *      ),
     *      @SWG\Parameter(
     *          name="offset",
     *          in="query",
     *          type="integer",
     *          description="The field used to specify blocks offset"
     *      )
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        return $this->response($this->serializer->serialize($this->getList($request), 'json'), Response::HTTP_OK);
    }

    /**
     * @Operation(
     *     summary="Returns block resource",
     *     tags={"Blockexplorer"},
     *
     *      @SWG\Response(
     *          response=422,
     *          description="Returned when Block Id is invalid"
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Returned when Block resource does not exist"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Returned when operation is successful",
     *          @Model(type=Block::class)
     *     ),
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="string",
     *          description="Block Id (hexadecimal number, e.g. 5B596520)"
     *     )
     * )
     *
     * @param string $id
     * @return Response
     */
    public function showAction(string $id): Response
    {
        if (!Block::validateId($id)) {
            throw new UnprocessableEntityHttpException(self::INVALID_RESOURCE_MESSAGE);
        }

        $block = $this->repository->getBlock($id);

        if (!$block) {
            throw new NotFoundHttpException(sprintf('The requested resource: %s was not found', $id));
        }

        return $this->response($this->serializer->serialize($block, 'json'), Response::HTTP_OK);
    }

    /**
     * @Operation(
     *     summary="List of messages for given block",
     *     tags={"Blockexplorer"},
     *
     *      @SWG\Response(
     *          response=422,
     *          description="Returned when Block Id is invalid"
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Returned when query parameters are invalid"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Returned when operation is successful",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref=@Model(type=Message::class))
     *          )
     *      ),
     *     @SWG\Parameter(
     *          name="sort",
     *          in="query",
     *          type="string",
     *          description="The field used to sort messages"
     *      ),
     *      @SWG\Parameter(
     *          name="order",
     *          in="query",
     *          type="string",
     *          description="The field used to set ordering for messages"
     *      ),
     *      @SWG\Parameter(
     *          name="limit",
     *          in="query",
     *          type="integer",
     *          description="The field used to limit number of messages"
     *      ),
     *      @SWG\Parameter(
     *          name="offset",
     *          in="query",
     *          type="integer",
     *          description="The field used to specify messages offset"
     *      ),
     *      @SWG\Parameter(
     *          name="blockId",
     *          in="path",
     *          type="string",
     *          description="Block Id (hexadecimal number, e.g. 5B758BC0)"
     *      )
     * )
     *
     * @param Request $request
     * @param string $blockId
     * @return Response
     */
    public function messagesAction(Request $request, string $blockId): Response
    {
        if (!Block::validateId($blockId)) {
            throw new UnprocessableEntityHttpException(self::INVALID_RESOURCE_MESSAGE);
        }

        $this->validateRequest($request, $this->messageRepository->availableSortingFields());

        $sort = $this->getSort($request);
        $order = $this->getOrder($request);
        $limit = $this->getLimit($request);
        $offset = $this->getOffset($request);

        $transactions = $this->messageRepository->getMessagesByBlockId(
            $blockId,
            $sort,
            $order,
            $limit,
            $offset
        );

        return $this->response($this->serializer->serialize($transactions, 'json'), Response::HTTP_OK);
    }

    /**
     * @Operation(
     *     summary="List of transactions for given block",
     *     tags={"Blockexplorer"},
     *
     *      @SWG\Response(
     *          response=422,
     *          description="Returned when Block Id is invalid"
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Returned when query parameters are invalid"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Returned when operation is successful",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref=@Model(type=Transaction::class))
     *          )
     *      ),
     *      @SWG\Parameter(
     *          name="blockId",
     *          in="path",
     *          type="string",
     *          description="Block Id (hexadecimal number, e.g. 5B758BC0)"
     *      ),
     *      @SWG\Parameter(
     *          name="hideConnections",
     *          in="query",
     *          type="boolean",
     *          description="The field used to hide connect transactions"
     *      ),
     *     @SWG\Parameter(
     *          name="sort",
     *          in="query",
     *          type="string",
     *          description="The field used to sort transactions"
     *      ),
     *      @SWG\Parameter(
     *          name="order",
     *          in="query",
     *          type="string",
     *          description="The field used to set ordering for transactions"
     *      ),
     *      @SWG\Parameter(
     *          name="limit",
     *          in="query",
     *          type="integer",
     *          description="The field used to limit number of transactions"
     *      ),
     *      @SWG\Parameter(
     *          name="offset",
     *          in="query",
     *          type="integer",
     *          description="The field used to specify transactions offset"
     *      )
     * )
     *
     * @param Request $request
     * @param string $blockId
     * @return Response
     */
    public function transactionsAction(Request $request, string $blockId): Response
    {
        if (!Block::validateId($blockId)) {
            throw new UnprocessableEntityHttpException(self::INVALID_RESOURCE_MESSAGE);
        }

        $this->validateRequest($request, $this->transactionRepository->availableSortingFields());

        $sort = $this->getSort($request);
        $order = $this->getOrder($request);
        $limit = $this->getLimit($request);
        $offset = $this->getOffset($request);
        $hideConnections = (bool)$request->get('hideConnections', false);

        $transactions = $this->transactionRepository->getTransactionsByBlockId(
            $blockId,
            $hideConnections,
            $sort,
            $order,
            $limit,
            $offset
        );

        return $this->response($this->serializer->serialize($transactions, 'json'), Response::HTTP_OK);
    }
}
