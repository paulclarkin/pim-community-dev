<?php

namespace Oro\Bundle\DataAuditBundle\Tests;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestDataAuditApiTest extends WebTestCase
{

    /** @var Client */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    public function tearDown()
    {
        unset($this->client);
    }

    /**
     * @return array
     */
    public function testPreconditions()
    {
        //clear Audits
        $this->client->request('GET', $this->client->generate('oro_api_get_audits'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        foreach ($result as $audit) {
            $this->client->request('DELETE', $this->client->generate('oro_api_delete_audit', array('id' => $audit['id'])));
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 204);
        }

        //create users
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => '1',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );

        $this->client->request('POST', $this->client->generate('oro_api_post_user'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $response
     * @return array
     * @depends testPreconditions
     */
    public function testGetAudits($response)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_audits'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $resultExpected = reset($result);
        $this->assertEquals($resultExpected['action'], 'create');
        $this->assertEquals($resultExpected['object_class'], 'Oro\Bundle\UserBundle\Entity\User');
        $this->assertEquals($resultExpected['object_name'], $response['user']['username']);
        $this->assertEquals($resultExpected['user'], 'admin');
        $this->assertEquals($resultExpected['data']['username']['new'], $response['user']['username']);
        $this->assertEquals($resultExpected['data']['email']['new'], $response['user']['email']);
        $this->assertEquals($resultExpected['data']['enabled']['new'], $response['user']['enabled']);
        $this->assertEquals($resultExpected['data']['roles']['new'], 'User');

        return $result;
    }

    /**
     * @param  array $response
     * @depends testGetAudits
     */
    public function testGetAudit($response)
    {
        foreach ($response as $audit) {
            $this->client->request('GET', $this->client->generate('oro_api_get_audit', array('id' => $audit['id'])));
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200);
            $result = ToolsAPI::jsonToArray($result->getContent());
            unset($result['loggedAt']);
            unset($audit['loggedAt']);
            $this->assertEquals($audit, $result);
        }
    }

    /**
     * @param array $response
     * @depends testGetAudits
     */
    public function testDeleteAudit($response)
    {
        foreach ($response as $audit) {
            $this->client->request('DELETE', $this->client->generate('oro_api_delete_audit', array('id' => $audit['id'])));
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 204);
        }
        $this->client->request('GET', $this->client->generate('oro_api_get_audits'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEmpty($result);
    }
}
