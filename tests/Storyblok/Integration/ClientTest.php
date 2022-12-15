<?php

use Storyblok\Client;

test('Integration: space info', function () {
    $client = new Client('Iw3XKcJb6MwkdZEwoQ9BCQtt');
    $space = $client->get('spaces/me/', $client->getApiParameters());
    $this->assertArrayHasKey('space', $space->httpResponseBody);
    $this->assertCount(5, $space->httpResponseBody['space']);
    $this->assertEquals('40101', $space->httpResponseBody['space']['id']);
})->setGroups(['integration']);

test('Integration: get All stories', function () {
    $client = new Client('Iw3XKcJb6MwkdZEwoQ9BCQtt');
    $options = $client->getApiParameters();
    $options['per_page'] = 3;
    $responses = $client->getAll('stories/', $options);
    $this->assertCount(3, $responses);
    $stories = [];
    foreach ($responses as $response) {
        array_push($stories, ...$response->httpResponseBody['stories']);
    }
    $this->assertCount(8, $stories);
})->setGroups(['integration']);
