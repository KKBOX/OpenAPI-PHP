<?php

namespace KKBOX\KKBOXOpenAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

abstract class Territory
{
    const Taiwan = 'TW';
    const HongKong = 'HK';
    const Singapore = 'SG';
    const Malaysia = 'MY';
    const Japan = 'JP';
}

abstract class SearchType
{
    const Track = 'track';
    const Album = 'album';
    const Artist = 'artist';
    const Playlist = 'playlist';
}

class AccessToken
{
    protected $accessToken = null;
    protected $tokenType = null;
    protected $expiresIn = null;

    public function __construct($accessToken, $tokenType, $expiresIn)
    {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function getExpiresIn()
    {
        return $this->expiresIn;
    }
}

class OpenAPI
{
    protected $clientID = null;
    protected $clientSecret = null;
    protected $accessToken = null;

    const API_END_POINT = "https://api.kkbox.com/v1.1";

    public function __construct($clientID, $clientSecret)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Fetch a new access token by client credential flow.
     * */
    public function fetchAccessTokenByClientCredential()
    {
        assert(!empty($this->clientID));
        assert(!empty($this->clientSecret));
        $base = $this->clientID . ":" . $this->clientSecret;
        $credentials = base64_encode($base);
        $client = new Client();
        $headers = [
            'Authorization' => 'Basic ' . $credentials,
            'Content-type' => 'application/x-www-form-urlencoded',
            'User-Agent' => 'KKBOX Open API PHP SDK',
        ];
        $body = 'grant_type=client_credentials';
        $request = new Request('POST', 'https://account.kkbox.com/oauth2/token', $headers, $body);
        return $client->send($request);
    }

    /**
     * Fetch a new access token and assign it to the instance.
     * */
    public function fetchAndUpdateAccessToken()
    {
        $response = $this->fetchAccessTokenByClientCredential();
        if ($response->getStatusCode() != 200) {
            return false;
        }
        $jsonObject = json_decode($response->getBody());
        $accessToken = $jsonObject->access_token;
        $tokenType = $jsonObject->token_type;
        $expiresIn = $jsonObject->expires_in;
        if ($accessToken === null ||
            $tokenType === null ||
            $expiresIn === null) {
            return false;
        }
        $accessTokenObject = new \KKBOX\KKBOXOpenAPI\AccessToken($accessToken, $tokenType, $expiresIn);
        $this->accessToken = $accessTokenObject;
    }

    /**
     *  Setter of the access token.
     *  @param mixed $accessToken the new access token.
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /** Getter of the access token. */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    private function fetch($url)
    {
        $accessToken = $this->getAccessToken()->getAccessToken();
        assert($accessToken != null);
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'User-Agent' => 'KKBOX Open API PHP SDK',
        ];
        $request = new Request('GET', $url, $headers);
        return $client->send($request);
    }

    /**
     * Fetch a track from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference.
     * @param string $trackID the ID of the track.
     * @param string $territory current territory.
     * */
    public function fetchTrack($trackID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/tracks/$trackID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch an album from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#albums-album_id
     * @param string $albumID the ID of the album.
     * @param string $territory current territory.
     */
    public function fetchAlbum($albumID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/albums/$albumID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch an album from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#albums-album_id-tracks
     * @param string $albumID the ID of the album.
     * @param string $territory current territory.
     */
    public function fetchTracksInAlbum($albumID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/albums/$albumID/tracks?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch an artist from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#artists-artist_id
     * @param string $artistID ID of the artist.
     * @param string $territory current territory.
     */
    public function fetchArtist($artistID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/artists/$artistID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch albums of an artist from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#artists-artist_id-albums
     * @param string $artistID ID of the artist
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 200.
     */
    public function fetchAlbumsOfArtist($artistID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 200) {
        $url = self::API_END_POINT . "/artists/$artistID/albums?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch top tracks of an artist.
     * See https://docs-en.kkbox.codes/v1.1/reference#artists-artist_id-toptracks
     * @param string $artistID ID of the artist.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 200.
     */
    public function fetchTopTracksOfArtist($artistID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 200) {
        $url = self::API_END_POINT . "/artists/$artistID/top-tracks?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch related artists of an artist.
     * See https://docs-en.kkbox.codes/v1.1/reference#artists-artist_id-relatedartists
     * @param string $artistID ID of the artist.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 20,
     */
    public function fetchRelatedArtistsOfArtist($artistID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 20) {
        $url = self::API_END_POINT . "/artists/$artistID/related-artists?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch metadata and tracks of a playlist from Open API
     * See https://docs-en.kkbox.codes/v1.1/reference#shared-playlists
     * @param string $playlistID ID of the playlist.
     * @param string $territory current territory.
     */
    public function fetchPlaylist($playlistID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/shared-playlists/$playlistID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch tracks in a playlist from Open API
     * See https://docs-en.kkbox.codes/v1.1/reference#sharedplaylists-playlist_id-tracks
     * @param string $playlistID ID of the playlist.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 20,
     */
    public function fetchTrackInPlaylist($playlistID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 20) {
        $url = self::API_END_POINT . "/shared-playlists/$playlistID/tracks?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch featured playlists
     * See https://docs-en.kkbox.codes/v1.1/reference#featured-playlists
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchFeaturedPlaylists($territory = Territory::Taiwan, $offset = 0, $limit = 100)
    {
        $url = self::API_END_POINT . "/featured-playlists?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch playlist categories from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#featuredplaylistcategories
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchFeaturedPlaylistCategories($territory = Territory::Taiwan, $offset = 0, $limit = 100)
    {
        $url = self::API_END_POINT . "/featured-playlist-categories?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch metadata of a category from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#featuredplaylistcategories-category_id
     * @param string $categoryID ID of the category.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     *  */
    public function fetchFeaturedPlaylistCategory($categoryID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 100) {
        $url = self::API_END_POINT . "/featured-playlist-categories/$categoryID?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch playlists in a featured playlist category.
     * See https://docs-en.kkbox.codes/v1.1/reference#featuredplaylistcategories-category_id-playlists
     * @param string $categoryID ID of the category.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchPlaylistsInFeaturedPlaylistCategory($categoryID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 100) {
        $url = self::API_END_POINT . "/featured-playlist-categories/$categoryID/playlists?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch new hits playlists from Open API.
     * See https: //docs-en.kkbox.codes/v1.1/reference#new-hits-playlists
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 10,
     */
    public function fetchNewHitsPlaylists($territory = Territory::Taiwan, $offset = 0, $limit = 10)
    {
        $url = self::API_END_POINT . "/new-hits-playlists?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch charts from Open API.
     * See https: //docs-en.kkbox.codes/v1.1/reference#charts_1
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 50,
     */
    public function fetchCharts($territory = Territory::Taiwan, $offset = 0, $limit = 50)
    {
        $url = self::API_END_POINT . "/charts?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch mood stations from Open API.
     * See https: //docs-en.kkbox.codes/v1.1/reference#moodstations

     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchMoodStations($territory = Territory::Taiwan, $offset = 0, $limit = 100)
    {
        $url = self::API_END_POINT . "/mood-stations?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch a mood station by giving an ID from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#moodstations-station_id
     * @param string $stationID ID of the station.
     * @param string $territory current territory.
     */
    public function fetchMoodStation($stationID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/mood-stations/$stationID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch genre stations from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#genrestations
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchGenreStations($territory = Territory::Taiwan, $offset = 0, $limit = 100)
    {
        $url = self::API_END_POINT . "/genre-stations?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch a genre station by giving an ID from Open API.
     * See https://docs-en.kkbox.codes/v1.1/reference#genrestations-station_id
     * @param string $stationID ID of the station.
     * @param string $territory current territory.
     */
    public function fetchGenreStation($stationID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/genre-stations/$stationID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch new released album categories
     * See https://docs-en.kkbox.codes/v1.1/reference#newreleasecategories
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchNewReleaseAlbumCategories($territory = Territory::Taiwan, $offset = 0, $limit = 100)
    {
        $url = self::API_END_POINT . "/new-release-categories?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Fetch new released album category by giving the category ID.
     * See https://docs-en.kkbox.codes/v1.1/reference#newreleasecategories-category_id
     * @param string $categoryID ID of the category.
     * @param string $territory current territory.
     */
    public function fetchNewReleaseAlbumCategory($categoryID,
        $territory = Territory::Taiwan) {
        $url = self::API_END_POINT . "/new-release-categories/$categoryID?territory=$territory";
        return $this->fetch($url);
    }

    /**
     * Fetch new released album category by giving the category ID.
     * See https://docs-en.kkbox.codes/v1.1/reference#newreleasecategories-category_id-albums
     * @param string $categoryID ID of the category.
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function fetchAlbumsUnderNewReleaseAlbumCategory($categoryID,
        $territory = Territory::Taiwan, $offset = 0, $limit = 100) {
        $url = self::API_END_POINT . "/new-release-categories/$categoryID/albums?territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

    /**
     * Search in Open API.
     * See https: //docs-en.kkbox.codes/v1.1/reference#search_1
     * @param string $territory current territory.
     * @param int $offset the begin of the page of the response. Default 0.
     * @param int $limit the max amount of the items in the response. Default 100,
     */
    public function search($keyword,
        $searchTypes = [SearchType::Track, SearchType::Album, SearchType::Artist, SearchType::Playlist],
        $territory = Territory::Taiwan, $offset = 0, $limit = 50) {
        $keyword = trim($keyword);
        assert(strlen($keyword) > 0);
        $types = implode(',', $searchTypes);
        $url = self::API_END_POINT . "/search?q=$keyword&type=$types&territory=$territory&offset=$offset&limit=$limit";
        return $this->fetch($url);
    }

}
