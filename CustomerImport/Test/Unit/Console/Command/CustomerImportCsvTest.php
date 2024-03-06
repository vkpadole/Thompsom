<?php

namespace Thompsom\CustomerImport\Test\Unit\Console\Command;

use PHPUnit\Framework\TestCase;
use Thompsom\CustomerImport\Console\Command\CustomerImportCsv;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Symfony\Component\Console\Output\BufferedOutput;

class CustomerImportCsvTest extends TestCase
{
    public function testExecuteWithNonExistentFile()
    {
        // Mock dependencies
        $customerFactoryMock = $this->createMock(CustomerInterfaceFactory::class);
        $customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $stateMock = $this->createMock(State::class);
        $directoryListMock = $this->createMock(DirectoryList::class);
        $csvProcessorMock = $this->createMock(Csv::class);
        $fileDriverMock = $this->createMock(FileDriver::class);


        // Set up expectations
        $fileDriverMock->method('isExists')->willReturn(false);

        // Create command instance with mocks
        $command = new CustomerImportCsv(
            $customerFactoryMock,
            $customerRepositoryMock,
            $stateMock,
            $directoryListMock,
            $csvProcessorMock,
            $fileDriverMock
        );

        // Create command tester and execute
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => 'sample.csv',
        ]);

        // Assert output
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error: File does not exist.', $output);
    }

}
