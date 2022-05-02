<?php

use Storyblok\GraphQLClient;

test(' GraphQLClient can be instanced', function () {
    $this->assertInstanceOf(GraphQLClient::class, new GraphQLClient('token'));
});

test('can query Space', function () {
    $gqClient = new GraphQLClient('token');
    $gqClient->space();
    expect($gqClient->getPayloadQuery())->toEqual('{
  Space {
    id
    domain
    languageCodes
    name
    version
  }
}');
});

test('PostItems per page and start with', function () {
    $gqClient = new GraphQLClient('test', $endpoint = 'gql_PostItems', $version = 'v1');
    $gqClient->mockable([
        mockResponse($endpoint, [], $version, 200),
    ]);

    $response = $gqClient->contents('post')->perPage(10)->folder('how-to/')->query();
    expect($gqClient->getPayloadQuery())->toEqual('{
  PostItems(per_page: 10, starts_with: "how-to/") {
    items {
      id
      name
    }
    total
  }}');
    $body = $gqClient->getBody();
    expect($body)->toHaveKey('data');
    expect($body['data'])->toHaveKey('PostItems');
    expect($body['data']['PostItems'])->toHaveKey('items');
    expect($body['data']['PostItems'])->toHaveKey('total');
    expect($body['data']['PostItems'])->toHaveCount(2);

});

