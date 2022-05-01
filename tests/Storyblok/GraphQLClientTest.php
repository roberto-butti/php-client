<?php

use Storyblok\GraphQLClient;

test(' GraphQLClient can be instanced', function () {
    $this->assertInstanceOf(GraphQLClient::class, new GraphQLClient('token'));
});

test(' can query Space', function () {
    $gqClient = new GraphQLClient('token');
    $gqClient->space();
    expect($gqClient->getPayloadQuery())->toEqual("{ 
  Space {
    id
    domain
    languageCodes
    name
    version
  }
 }");
});

