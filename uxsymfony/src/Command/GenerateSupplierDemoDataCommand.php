<?php

namespace App\Command;

use App\Entity\Supplier;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GenerateSupplierDemoDataCommand extends Command
{
    protected static $defaultName = 'app:generate-supplier-demo-data';
    
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private QiniuUploadService $qiniuService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        QiniuUploadService $qiniuService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->qiniuService = $qiniuService;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-supplier-demo-data')
            ->setDescription('Generate demo supplier data with various statuses and upload images to Qiniu Cloud');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 检查七牛云配置
        if (!$this->qiniuService->isConfigValid()) {
            $io->error('七牛云配置无效，请检查.env文件中的QINIU_AK、QINIU_SK和QINIU_BUCKET配置');
            return Command::FAILURE;
        }

        $io->info('开始生成供应商演示数据...');

        // 开始事务
        $this->entityManager->beginTransaction();
        try {
            // 上传示例图片到七牛云
            $businessLicenseImage = $this->uploadDemoImage('营业执照.jpg', 'business_license_');
            $idCardFrontImage = $this->uploadDemoImage('身份证1.webp', 'id_card_front_');
            $idCardBackImage = $this->uploadDemoImage('身份证2.jpg', 'id_card_back_');

            if (!$businessLicenseImage || !$idCardFrontImage || !$idCardBackImage) {
                $io->error('上传示例图片失败');
                $this->entityManager->rollback();
                return Command::FAILURE;
            }

            // 获取现有供应商数量
            $existingCount = $this->entityManager->createQuery('SELECT COUNT(s.id) FROM App\Entity\Supplier s')->getSingleScalarResult();
            $startNumber = $existingCount + 1;
            
            // 生成30条供应商数据
            for ($i = $startNumber; $i < $startNumber + 30; $i++) {
                $supplier = new Supplier();
                
                // 基本信息
                $supplier->setUsername("supplier{$i}");
                $supplier->setEmail("supplier{$i}@example.com");
                
                // 检查用户名和邮箱是否已存在
                $existingSupplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneBy(['username' => "supplier{$i}"]);
                if ($existingSupplier) {
                    $io->warning("供应商 supplier{$i} 已存在，跳过");
                    continue;
                }
                $supplier->setPassword($this->passwordHasher->hashPassword($supplier, '12345678'));
                $supplier->setContactPerson("联系人{$i}");
                $supplier->setContactPhone("1380013800{$i}");
                $supplier->setContactAddress("北京市朝阳区某某街道{$i}号");
                
                // 设置供应商类型（交替生成公司和个人供应商）
                if ($i % 2 === 1) {
                    // 公司供应商
                    $supplier->setSupplierType('company');
                    $supplier->setCompanyName("北京某某科技有限公司{$i}");
                    $supplier->setCompanyType(['factory', 'trader', 'factory_trader', 'brand'][rand(0, 3)]);
                    $supplier->setBusinessLicenseNumber("9111000012345678{$i}");
                    $supplier->setBusinessLicenseImage($businessLicenseImage);
                    $supplier->setLegalPersonName("法人姓名{$i}");
                    $supplier->setLegalPersonIdCard("11010119900101123{$i}");
                    $supplier->setLegalPersonIdFront($idCardFrontImage);
                    $supplier->setLegalPersonIdBack($idCardBackImage);
                    $supplier->setRegisteredCapital(rand(100, 10000));
                    $supplier->setEstablishmentDate(new \DateTime(sprintf('201%s-0%s-1%s', rand(0, 9), rand(1, 9), rand(1, 9))));
                    $supplier->setBusinessScope("电子产品、软件开发、技术服务");
                    $supplier->setBankAccountName("北京某某科技有限公司{$i}");
                    $supplier->setBankAccountNumber("6222021234567890{$i}");
                    $supplier->setBankName("中国工商银行北京分行");
                    $supplier->setBankBranch("中国工商银行北京朝阳支行");
                    $supplier->setTaxNumber("9111000012345678{$i}");
                } else {
                    // 个人供应商
                    $supplier->setSupplierType('individual');
                    $supplier->setIndividualName("个人供应商{$i}");
                    $supplier->setIndividualIdCard("11010119900101123{$i}");
                    $supplier->setIndividualIdFront($idCardFrontImage);
                    $supplier->setIndividualIdBack($idCardBackImage);
                    $supplier->setIndividualBankAccount("6222021234567890{$i}");
                    $supplier->setIndividualBankName("中国工商银行北京分行");
                }
                
                // 业务信息
                $supplier->setMainCategory("主营品类{$i}");
                $supplier->setHasCrossBorderExperience($i % 3 === 0);
                $supplier->setAnnualSalesVolume(rand(100, 5000));
                $supplier->setWarehouseAddress("北京市朝阳区某某仓库{$i}号");
                
                // 账号状态
                $supplier->setIsActive($i % 5 !== 0); // 每5个禁用一个
                $supplier->setIsVerified($i % 4 !== 0); // 每4个未验证邮箱一个
                
                // 审核状态（确保各种状态都有）
                $auditStatuses = ['pending', 'approved', 'rejected', 'resubmit'];
                $auditStatus = $auditStatuses[($i - 1) % 4];
                $supplier->setAuditStatus($auditStatus);
                
                // 审核备注（针对拒绝和重新提交状态）
                if ($auditStatus === 'rejected') {
                    $supplier->setAuditRemark("营业执照不清晰，请重新上传");
                } elseif ($auditStatus === 'resubmit') {
                    $supplier->setAuditRemark("缺少银行开户证明，请补充");
                }
                
                // 审核人和时间（针对已审核状态）
                if ($auditStatus === 'approved' || $auditStatus === 'rejected' || $auditStatus === 'resubmit') {
                    $supplier->setAuditedBy(1); // 假设管理员ID为1
                    $supplier->setAuditedAt(new \DateTime("-{$i} days"));
                }
                
                // 时间戳
                $supplier->setCreatedAt(new \DateTime("-{$i} days"));
                if ($i % 3 === 0) {
                    $supplier->setLastLoginAt(new \DateTime("-" . rand(1, $i) . " days"));
                }
                
                // 财务和会员信息
                $this->setFinancialAndMembershipData($supplier, $i);
                
                $this->entityManager->persist($supplier);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $io->success("成功生成供应商演示数据！");
            $io->note("其中包括：");
            $io->text("- 公司供应商：约15个");
            $io->text("- 个人供应商：约15个");
            $io->text("- 待审核状态：约7个");
            $io->text("- 已通过状态：约8个");
            $io->text("- 已拒绝状态：约7个");
            $io->text("- 待重新提交状态：约8个");
            $io->text("- 激活账号：约24个");
            $io->text("- 禁用账号：约6个");
            $io->text("- 已验证邮箱：约23个");
            $io->text("- 未验证邮箱：约7个");
            $io->text("- 财务和会员信息：所有供应商");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error('生成演示数据失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 设置财务和会员数据
     */
    private function setFinancialAndMembershipData(Supplier $supplier, int $index): void
    {
        // 生成余额数据
        $balance = rand(1000, 100000);
        $supplier->setBalance($balance);
        
        // 生成冻结余额数据（通常小于余额）
        $frozenBalance = rand(0, $balance * 0.3);
        $supplier->setBalanceFrozen($frozenBalance);
        
        // 生成会员类型数据
        $membershipTypes = ['none', 'monthly', 'quarterly', 'half_yearly', 'yearly'];
        $membershipType = $membershipTypes[array_rand($membershipTypes)];
        $supplier->setMembershipType($membershipType);
        
        // 生成会员到期时间（如果会员类型不是none）
        if ($membershipType !== 'none') {
            $daysToAdd = match($membershipType) {
                'monthly' => 30,
                'quarterly' => 90,
                'half_yearly' => 180,
                'yearly' => 365,
                default => 30
            };
            
            $expireDate = new \DateTime();
            $expireDate->modify("+{$daysToAdd} days");
            $supplier->setMembershipExpiredAt($expireDate);
        }
        
        // 生成佣金比例数据（30%概率使用自定义佣金比例）
        if (rand(1, 100) <= 30) {
            // 生成0.0001到0.1500之间的佣金比例（0.01%到15%）
            $commissionRate = rand(1, 1500) / 10000;
            $supplier->setCommissionRate(number_format($commissionRate, 4, '.', ''));
        }
    }

    /**
     * 上传演示图片到七牛云
     */
    private function uploadDemoImage(string $fileName, string $prefix): ?string
    {
        $demoImagePath = __DIR__ . '/../../aaaddd/' . $fileName;
        
        if (!file_exists($demoImagePath)) {
            return null;
        }
        
        // 生成唯一的文件名
        $uniqueFileName = $prefix . uniqid() . '_' . time() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        
        // 上传文件
        $result = $this->qiniuService->uploadFile($demoImagePath, $uniqueFileName);
        
        if ($result['success']) {
            return $result['key']; // 返回文件key而不是完整URL
        }
        
        return null;
    }
}