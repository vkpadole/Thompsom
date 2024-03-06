<?php
/**
 * Thompsom Software.
 *
 * @category  Thompsom
 * @package   Thompsom_CustomerImport
 * @author    Thompsom
 */

namespace Thompsom\CustomerImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

class CustomerImportCsv extends Command
{
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerInterfaceFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var FileDriver
     */
    protected $fileDriver;

    /**
     * Constructor.
     *
     * @param CustomerInterfaceFactory    $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param State                       $state
     * @param DirectoryList               $directoryList
     * @param Csv                         $csvProcessor
     * @param FileDriver                  $fileDriver
     */
    public function __construct(
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        State $state,
        DirectoryList $directoryList,
        Csv $csvProcessor,
        FileDriver $fileDriver
    ) {
        parent::__construct();
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->state = $state;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
         $this->setName('customer:import:sample-csv')
            ->setDescription('Import customers from CSV file')
            ->addArgument(
                'file',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Path to the CSV file'
            );
    }
    
    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvFile = $input->getArgument('file');
        $directoryRead = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $csvFilePath = $directoryRead . DIRECTORY_SEPARATOR . $csvFile;
        if (!$this->fileDriver->isExists($csvFilePath)) {
            $output->writeln('Error: File does not exist.');
            return;
        }
        $csvData = $this->csvProcessor->getData($csvFilePath);

        foreach ($csvData as $row => $data) {
            if ($row === 0) {
                continue; // Skip the header row
            }

            try {
                $customer = $this->customerInterfaceFactory->create();
                $customer->setFirstname($data[0]);
                $customer->setLastname($data[1]);
                $customer->setEmail($data[2]);
                $customer->setWebsiteId(1);
                $customer->setStoreId(1);
                $customer = $this->customerRepository->save($customer);
                $output->writeln('Customer ' . $customer->getEmail() . ' imported successfully.');
            } catch (\Exception $e) {
                $output->writeln('Error importing customer: ' . $e->getMessage());
            }
        }

        $output->writeln('Customer import process completed.');
    }
}
