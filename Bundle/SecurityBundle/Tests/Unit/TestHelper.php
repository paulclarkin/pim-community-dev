<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit;

use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\EntityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProviderStub;

class TestHelper
{
    public static function get(\PHPUnit_Framework_TestCase $testCase)
    {
        return new TestHelper($testCase);
    }

    /**
     * @var (\PHPUnit_Framework_TestCase
     */
    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $ownerTree
     * @return AclExtensionSelector
     */
    public function createAclExtensionSelector(
        OwnershipMetadataProvider $metadataProvider = null,
        OwnerTree $ownerTree = null
    ) {
        $classAccessor = new EntityClassAccessor();
        $idAccessor = new ObjectIdAccessor();
        $selector = new AclExtensionSelector($idAccessor);
        $selector->addAclExtension(
            new ActionAclExtension()
        );
        $selector->addAclExtension(
            $this->createEntityAclExtension($metadataProvider, $ownerTree, $classAccessor, $idAccessor)
        );

        return $selector;
    }

    /**
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $ownerTree
     * @param EntityClassAccessor $classAccessor
     * @param ObjectIdAccessor $idAccessor
     * @return EntityAclExtension
     */
    public function createEntityAclExtension(
        OwnershipMetadataProvider $metadataProvider = null,
        OwnerTree $ownerTree = null,
        EntityClassAccessor $classAccessor = null,
        ObjectIdAccessor $idAccessor = null
    ) {
        if ($classAccessor === null) {
            $classAccessor = new EntityClassAccessor();
        }
        if ($idAccessor === null) {
            $idAccessor = new ObjectIdAccessor();
        }
        if ($metadataProvider === null) {
            $metadataProvider = new OwnershipMetadataProviderStub($this->testCase);
        }
        if ($ownerTree === null) {
            $ownerTree = new OwnerTree();
        }

        $decisionMaker = new EntityOwnershipDecisionMaker(
            $ownerTree,
            $classAccessor,
            $idAccessor,
            new EntityOwnerAccessor($classAccessor, $metadataProvider),
            $metadataProvider
        );

        $config = $this->testCase->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->testCase->any())
            ->method('getEntityNamespaces')
            ->will(
                $this->testCase->returnValue(
                    array(
                        'Test' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity'
                    )
                )
            );

        $em = $this->testCase->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->testCase->any())
            ->method('getConfiguration')
            ->will($this->testCase->returnValue($config));

        $doctrine = $this->testCase->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->testCase->any())
            ->method('getManagers')
            ->will($this->testCase->returnValue(array('default' => $em)));
        $doctrine->expects($this->testCase->any())
            ->method('getManager')
            ->with($this->testCase->equalTo('default'))
            ->will($this->testCase->returnValue($em));
        $doctrine->expects($this->testCase->any())
            ->method('getAliasNamespace')
            ->will(
                $this->testCase->returnValueMap(
                    array(
                        array('Test', 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity'),
                    )
                )
            );

        return new EntityAclExtension(
            $classAccessor,
            $idAccessor,
            new EntityClassResolver($doctrine),
            $metadataProvider,
            $decisionMaker
        );
    }
}
