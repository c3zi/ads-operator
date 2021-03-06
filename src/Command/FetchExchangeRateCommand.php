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

declare(strict_types=1);

namespace Adshares\AdsOperator\Command;

use Adshares\AdsOperator\Exchange\Exception\ProviderRuntimeException;
use Adshares\AdsOperator\Exchange\Provider\Provider;
use Adshares\AdsOperator\UseCase\Exchange\UpdateExchangeRate;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class FetchExchangeRateCommand extends ContainerAwareCommand
{
    private const COIN_GECKO = 'coin_gecko';

    private const SUPPORTED_PROVIDERS = Provider::PROVIDER_LIST;

    /** @var UpdateExchangeRate */
    private $useCase;

    public function __construct(UpdateExchangeRate $useCase)
    {
        $this->useCase = $useCase;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'provider',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which provider do you want to use',
                self::COIN_GECKO
            )
            ->setName('ops:exchange:import')
            ->setDescription('Importing exchange rate from provider');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Starting importing an exchange rate.');
        $providerName = $input->getOption('provider');

        if (!array_key_exists($providerName, self::SUPPORTED_PROVIDERS)) {
            $output->writeln(sprintf('Provider `%s` is not supported.', $providerName));
        }

        try {
            $this->useCase->update(new DateTime(), $providerName);
        } catch (ProviderRuntimeException $exception) {
            $output->writeln(sprintf('[Error] %s', $exception->getMessage()));
        }

        $output->writeln('Finished importing an exchange rate.');
    }
}
