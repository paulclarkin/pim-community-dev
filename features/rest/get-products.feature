Feature: Expose all product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose all product data via a REST API

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku     | name-en_US | description-en_US-mobile | description-en_US-tablet | price-EUR | price-USD | categories      |
      | sandals | My sandals | My great sandals         | My great new sandals     | 20        | 30        | 2014_collection |
      | shirt   | My shirt   | My great shirt           | My great new shirt       | 10        | 20        | 2014_collection |

  Scenario: Fail to authenticate an anonymous user
    Given I send a GET request to "api/rest/products"
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/products"
    Then the response code should be 200

  Scenario: Successfully retrieve a product
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I request information for all products
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    [{
      "family":null,
      "groups":[],
      "variant_group":null,
      "categories":["2014_collection"],
      "enabled":true,
      "associations":[],
      "values": {
        "sku":[
          {"locale":null,"scope":null,"data":"sandals"}
        ],
        "name":[
          {"locale":"en_US","scope":null,"data":"My sandals"}
        ],
        "description":[
          {"locale":"en_US","scope":"mobile","data":"My great sandals"},
          {"locale":"en_US","scope":"tablet","data":"My great new sandals"}
        ],
        "price":[
          {"locale":null,"scope":null,"data":[
            {"data":"20.00","currency":"EUR"},
            {"data":"30.00","currency":"USD"}
          ]}
        ]
      },
      "resource":"{baseUrl}/api/rest/products/sandals"
    },
        {
      "family":null,
      "groups":[],
      "variant_group":null,
      "categories":["2014_collection"],
      "enabled":true,
      "associations":[],
      "values": {
        "sku":[
          {"locale":null,"scope":null,"data":"shirt"}
        ],
        "name":[
          {"locale":"en_US","scope":null,"data":"My shirt"}
        ],
        "description":[
          {"locale":"en_US","scope":"mobile","data":"My great shirt"},
          {"locale":"en_US","scope":"tablet","data":"My great new shirt"}
        ],
        "price":[
          {"locale":null,"scope":null,"data":[
            {"data":"10.00","currency":"EUR"},
            {"data":"20.00","currency":"USD"}
          ]}
        ]
      },
      "resource":"{baseUrl}/api/rest/products/shirt"
    }]
    """
