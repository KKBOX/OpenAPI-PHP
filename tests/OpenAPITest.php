<?php

namespace KKBOX\KKBOXOpenAPI\Tests;

use KKBOX\KKBOXOpenAPI\OpenAPI;
use PHPUnit\Framework\TestCase;

class OpenAPITest extends TestCase
{
    protected $openAPI = null;

    protected function setUp()
    {
        $clientID = '5fd35360d795498b6ac424fc9cb38fe7';
        $clientSecret = '8bb68d0d1c2b483794ee1a978c9d0b5d';
        $this->openAPI = new OpenAPI($clientID, $clientSecret);
    }

    // ----

    public function testFetchAccessToken()
    {
        $response = $this->openAPI->fetchAccessTokenByClientCredential();
        $this->assertTrue($response->getStatusCode() === 200);
        $jsonObject = json_decode($response->getBody());
        $this->assertTrue($jsonObject->access_token !== null);
        $this->assertTrue($jsonObject->token_type !== null);
        $this->assertTrue($jsonObject->expires_in !== null);
    }

    public function testFetchAndUpdateAccessToken()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $accessToken = $this->openAPI->getAccessToken();
        $this->assertTrue($accessToken !== null);
        $this->assertTrue($accessToken->getAccessToken() !== null);
        $this->assertTrue($accessToken->getTokenType() !== null);
        $this->assertTrue($accessToken->getExpiresIn() !== null);
    }

    // ----

    private function validateTrack($track)
    {
        $this->assertTrue($track->id !== null);
        $this->assertTrue($track->name !== null);
        $this->assertTrue($track->duration !== null);
        $this->assertTrue($track->url !== null);
        $this->assertTrue($track->track_number !== null);
        $this->assertTrue($track->explicitness !== null);
        $this->assertTrue($track->available_territories !== null);
        // $this->assertTrue($track->album !== null);
        if (property_exists($track, 'album')) {
            $this->validateAlbum($track->album);
        }
    }

    private function validateAlbum($album)
    {
        $this->assertTrue($album->id !== null);
        $this->assertTrue($album->name !== null);
        $this->assertTrue($album->url !== null);
        $this->assertTrue($album->explicitness !== null);
        $this->assertTrue($album->available_territories !== null);
        // $this->assertTrue($album->release_date !== null);
        // Note: release date is not available from the album data attached to tracks in genre stations
        $this->assertTrue($album->images !== null);
        $this->assertTrue($album->artist !== null);
        foreach ($album->images as $image) {
            $this->validateImage($image);
        }
        $this->validateArtist($album->artist);
    }

    private function validateArtist($artist)
    {
        $this->assertTrue($artist->id !== null);
        $this->assertTrue($artist->name !== null);
        $this->assertTrue($artist->url !== null);
        $this->assertTrue($artist->images !== null);
        foreach ($artist->images as $image) {
            $this->validateImage($image);
        }
    }

    private function validateImage($images)
    {
        $this->assertTrue($images->height !== null);
        $this->assertTrue($images->width !== null);
        $this->assertTrue($images->url !== null);
    }

    private function validatePlaylist($playlist)
    {
        $this->assertTrue($playlist->id !== null);
        $this->assertTrue($playlist->title !== null);
        $this->assertTrue($playlist->description !== null);
        $this->assertTrue($playlist->url !== null);
        $this->assertTrue($playlist->images !== null);
        foreach ($playlist->images as $image) {
            $this->validateImage($image);
        }
    }

    // ----

    public function testFetchTrack()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchTrack('4kxvr3wPWkaL9_y3o_');
        $this->assertTrue($response->getStatusCode() === 200);
        $track = json_decode($response->getBody());
        $this->validateTrack($track);
    }

    public function testFetchAlbum()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchAlbum('WpTPGzNLeutVFHcFq6');
        $this->assertTrue($response->getStatusCode() === 200);
        $album = json_decode($response->getBody());
        $this->validateAlbum($album);
    }

    public function testFetchTracksInAlbum()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchTracksInAlbum('WpTPGzNLeutVFHcFq6');
        $this->assertTrue($response->getStatusCode() === 200);
        $tracks = json_decode($response->getBody());
        foreach ($tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchArtist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchArtist('8q3_xzjl89Yakn_7GB');
        $this->assertTrue($response->getStatusCode() === 200);
        $artist = json_decode($response->getBody());
        $this->validateArtist($artist);
    }

    public function testFetchAlbumsOfArtist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchAlbumsOfArtist('8q3_xzjl89Yakn_7GB');
        $this->assertTrue($response->getStatusCode() === 200);
        $albums = json_decode($response->getBody());
        foreach ($albums->data as $album) {
            $this->validateAlbum($album);
        }
    }

    public function testFetchTopTracksOfArtist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchTopTracksOfArtist('8q3_xzjl89Yakn_7GB');
        $this->assertTrue($response->getStatusCode() === 200);
        $tracks = json_decode($response->getBody());
        foreach ($tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchRelatedArtistsOfArtist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchRelatedArtistsOfArtist('8q3_xzjl89Yakn_7GB');
        $this->assertTrue($response->getStatusCode() === 200);
        $artists = json_decode($response->getBody());
        foreach ($artists->data as $artist) {
            $this->validateArtist($artist);
        }
    }

    public function testFetchPlaylist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchPlaylist('OsyceCHOw-NvK5j6Vo');
        $this->assertTrue($response->getStatusCode() === 200);
        $playlist = json_decode($response->getBody());
        $this->validatePlaylist($playlist);
        foreach ($playlist->tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchTracksInPlaylist()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchTrackInPlaylist('OsyceCHOw-NvK5j6Vo');
        $this->assertTrue($response->getStatusCode() === 200);
        $tracks = json_decode($response->getBody());
        foreach ($tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchFeaturedPlaylists()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchFeaturedPlaylists();
        $this->assertTrue($response->getStatusCode() === 200);
        $playlists = json_decode($response->getBody());
        foreach ($playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }
    }

    public function testFetchNewHitsPlaylists()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchNewHitsPlaylists();
        $this->assertTrue($response->getStatusCode() === 200);
        $playlists = json_decode($response->getBody());
        foreach ($playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }
    }

    public function testFetchCharts()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchCharts();
        $this->assertTrue($response->getStatusCode() === 200);
        $playlists = json_decode($response->getBody());
        foreach ($playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }
    }

    public function testFetchPlaylistCategories()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchFeaturedPlaylistCategories();
        $this->assertTrue($response->getStatusCode() === 200);
        $categories = json_decode($response->getBody());
        foreach ($categories->data as $category) {
            $this->assertTrue($category->id !== null);
            $this->assertTrue($category->title !== null);
            $this->assertTrue($category->images !== null);
            foreach ($category->images as $image) {
                $this->validateImage($image);
            }
        }
    }

    public function testFetchFeaturedPlaylistCategory()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchFeaturedPlaylistCategory('CrBHGk1J1KEsQlPLoz');
        $this->assertTrue($response->getStatusCode() === 200);
        $category = json_decode($response->getBody());
        $this->assertTrue($category->id !== null);
        $this->assertTrue($category->title !== null);
        $this->assertTrue($category->images !== null);
        $this->assertTrue($category->playlists !== null);
        foreach ($category->playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }
    }

    public function testFetchPlaylistsInFeaturedPlaylistCategory()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchPlaylistsInFeaturedPlaylistCategory('CrBHGk1J1KEsQlPLoz');
        $this->assertTrue($response->getStatusCode() === 200);
        $playlists = json_decode($response->getBody());
        foreach ($playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }
    }

    public function testFetchMoodStations()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchMoodStations();
        $this->assertTrue($response->getStatusCode() === 200);
        $stations = json_decode($response->getBody());
        foreach ($stations->data as $station) {
            $this->assertTrue($station->id !== null);
            $this->assertTrue($station->name !== null);
            $this->assertTrue($station->images !== null);
            $this->assertTrue(count($station->images) === 1);
        }
    }

    public function testFetchMoodStation()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchMoodStation('4tmrBI125HMtMlO9OF');
        $this->assertTrue($response->getStatusCode() === 200);
        $station = json_decode($response->getBody());
        $this->assertTrue($station->id !== null);
        $this->assertTrue($station->name !== null);
        $this->assertTrue($station->images !== null);
        $this->assertTrue(count($station->images) === 1);
        foreach ($station->tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchGenreStations()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchGenreStations();
        $this->assertTrue($response->getStatusCode() === 200);
        $stations = json_decode($response->getBody());
        foreach ($stations->data as $station) {
            $this->assertTrue($station->id !== null);
            $this->assertTrue($station->name !== null);
        }
    }

    public function testFetchGenreStation()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchGenreStation('9ZAb9rkyd3JFDBC0wF');
        $this->assertTrue($response->getStatusCode() === 200);
        $station = json_decode($response->getBody());
        $this->assertTrue($station->id !== null);
        $this->assertTrue($station->name !== null);
        foreach ($station->tracks->data as $track) {
            $this->validateTrack($track);
        }
    }

    public function testFetchNewReleaseAlbumCategories()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchNewReleaseAlbumCategories();
        $this->assertTrue($response->getStatusCode() === 200);
        $categories = json_decode($response->getBody());
        foreach ($categories->data as $category) {
            $this->assertTrue($category->id !== null);
            $this->assertTrue($category->title !== null);
        }
    }

    public function testFetchNewReleaseAlbumCategory()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchNewReleaseAlbumCategory('0pGAIGDf5SqYh_SyHr');
        $this->assertTrue($response->getStatusCode() === 200);
        $category = json_decode($response->getBody());
        $this->assertTrue($category->id !== null);
        $this->assertTrue($category->title !== null);
        foreach ($category->albums->data as $album) {
            $this->validateAlbum($album);
        }
    }

    public function testFetchAlbumsUnderNewReleaseAlbumCategory()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->fetchAlbumsUnderNewReleaseAlbumCategory('0pGAIGDf5SqYh_SyHr');
        $this->assertTrue($response->getStatusCode() === 200);
        $albums = json_decode($response->getBody());
        foreach ($albums->data as $album) {
            $this->validateAlbum($album);
        }
    }

    public function testSearch()
    {
        $this->openAPI->fetchAndUpdateAccessToken();
        $response = $this->openAPI->search('Love');
        $this->assertTrue($response->getStatusCode() === 200);
        $searchResults = json_decode($response->getBody());

        $this->assertTrue($searchResults->tracks !== null);
        foreach ($searchResults->tracks->data as $track) {
            $this->validateTrack($track);
        }

        $this->assertTrue($searchResults->albums !== null);
        foreach ($searchResults->albums->data as $album) {
            $this->validateAlbum($album);
        }

        $this->assertTrue($searchResults->artists !== null);
        foreach ($searchResults->artists->data as $artist) {
            $this->validateArtist($artist);
        }

        $this->assertTrue($searchResults->playlists !== null);
        foreach ($searchResults->playlists->data as $playlist) {
            $this->validatePlaylist($playlist);
        }

    }

}
