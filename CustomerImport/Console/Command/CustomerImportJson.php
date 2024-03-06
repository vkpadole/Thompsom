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
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\Io\File;

class CustomerImportJson extends Command
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
     * @var ReadFactory
     */
    protected $fileReadFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param CustomerInterfaceFactory    $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param State                       $state
     * @param DirectoryList               $directoryList
     * @param ReadFactory                 $fileReadFactory
     * @param File                        $file
     */
    public function __construct(
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        State $state,
        DirectoryList $directoryList,
        ReadFactory $fileReadFactory,
        File $file
    ) {
        parent::__construct();
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->state = $state;
        $this->directoryList = $directoryList;
        $this->fileReadFactory = $fileReadFactory;
        $this->file = $file;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('customer:import:sample-json')
            ->setDescription('Import customers from JSON file')
            ->addArgument(
                'file',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Path to the JSON file'
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
        $filePath = $input->getArgument('file');
        $file = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . $filePath;

        if (!$this->file->fileExists($file)) {
            $output->writeln('Error: File does not exist.');
            return;
        }
        // Create the file reader object
        $fileReader = $this->fileReadFactory->create($file, 'r');
        
        $jsonData = $fileReader->readAll();
        $customers = json_decode($jsonData, true);

        foreach ($customers as $customerData) {
            try {
                $customer = $this->customerInterfaceFactory->create();
                $customer->setFirstname($customerData['fname']);
                $customer->setLastname($customerData['lname']);
                $customer->setEmail($customerData['emailaddress']);
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
