<?php

namespace BookiePlatform\BetAdvanced;

use BookiePlatform\BetAdvanced\Exception\BetAdvancedException;
use BookiePlatform\Commons\HttpTools;
use BookiePlatform\Commons\Tools;
use BookiePlatform\Core\Funds;
use BookiePlatform\Core\Platform;
use BookiePlatform\Core\Player;
use BookiePlatform\Core\User;
use BookiePlatform\GameProvider\Model\FrontGame;
use BookiePlatform\PaymentGateway\BetAdvanced\PaymentGateway;
use BookiePlatform\Security\SecurityManager;

class BetAdvancedApi {

    const API_BET_ADVANCED = 'bet_advanced';

    const METHOD_GET_NETENT_DESKTOP_GAMES = 'get_netent_desktop_games';
    const METHOD_GET_NETENT_MOBILE_GAMES = 'get_netent_mobile_games';
    const METHOD_GET_EGT_DESKTOP_GAMES = 'get_egt_desktop_games';
    const METHOD_GET_EGT_MOBILE_GAMES = 'get_egt_mobile_games';
    const METHOD_GET_NOVOMATIC_DESKTOP_GAMES = 'get_novomatic_desktop_games';
    const METHOD_GET_NOVOMATIC_MOBILE_GAMES = 'get_novomatic_mobile_games';
    const METHOD_GET_AMATIC_DESKTOP_GAMES = 'get_amatic_desktop_games';
    const METHOD_GET_AMATIC_MOBILE_GAMES = 'get_amatic_mobile_games';

    const METHOD_GET_GAME_LINK = 'get_game_link';
    const METHOD_GET_BALANCE = 'getbalance';
    const METHOD_WRITE_BET = 'writebet';

    const BRAND_NETENT = 10;
    const BRAND_EGT = 11;
    const BRAND_NOVOMATIC = 12;
    const BRAND_AMATIC = 13;

    const TYPE_BET = 'bet';
    const TYPE_WIN = 'win';

    const EXCLUDED_GAME_IDS = [
        1243,
        1244,
        1353
    ];

    /**
     * @var BetAdvancedApi
     */
    protected static $instance;

    private function __construct() {

    }

    public function __clone() {
        throw new \Exception(_('Clone is not allowed'));
    }

    /**
     * @return BetAdvancedApi
     */
    public static function singleton() {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function processRequest($method, $params = null) {
        $result = '';
        try {
            switch ($method) {
                case self::METHOD_GET_NETENT_DESKTOP_GAMES:
                    $result = $this->getDesktopNetentGames();
                    break;
                case self::METHOD_GET_NETENT_MOBILE_GAMES:
                    $result = $this->getMobileNetentGames();
                    break;
                case self::METHOD_GET_EGT_DESKTOP_GAMES:
                    $result = $this->getDesktopEGTGames();
                    break;
                case self::METHOD_GET_EGT_MOBILE_GAMES:
                    $result = $this->getMobileEGTGames();
                    break;
                case self::METHOD_GET_NOVOMATIC_DESKTOP_GAMES:
                    $result = $this->getDesktopNovomaticGames();
                    break;
                case self::METHOD_GET_NOVOMATIC_MOBILE_GAMES:
                    $result = $this->getMobileNovomaticGames();
                    break;
                case self::METHOD_GET_AMATIC_DESKTOP_GAMES:
                    $result = $this->getDesktopAmaticGames();
                    break;
                case self::METHOD_GET_AMATIC_MOBILE_GAMES:
                    $result = $this->getMobileAmaticGames();
                    break;
                case self::METHOD_GET_GAME_LINK:
                    $result = $this->getGameLink($params);
                    break;
                case self::METHOD_GET_BALANCE:
                    $result = $this->getBalance($params);
                    break;
                case self::METHOD_WRITE_BET:
                    $result = $this->writeBet($params);
                    break;
            }
        } catch (\Exception $e) {
            Tools::debugLog('bet_advanced', "ERROR: " . print_r($e->getMessage(),1));
            $result = json_encode([
                'error' => 1,
                'error_message' => 'Please try again later'
            ]);
        }
        Tools::debugLog('bet_advanced', "RESPONSE: " . print_r(['method' => $method, 'params' => $params, 'res' => $result],1));
        return $result;
    }

    private function isGameExcluded($game) {
        return in_array($game->Id, self::EXCLUDED_GAME_IDS) ? true : false;
    }

    private function getDesktopNetentGames() {
        $games = $this->getGamesList();
        $desktop_games = [];
        foreach ($games as $game) {
if ($game->LaunchId == 'football_not_mobile') {continue;}
            if($game->IsDesktop && $game->Brand == self::BRAND_NETENT && !$this->isGameExcluded($game)) {
                $desktop_games[] = $this->correctGameInfo($game, 'netent', 'desktop');
            }
        }
        return $desktop_games;
    }

    private function getMobileNetentGames() {
        $games = $this->getGamesList();
        $mobile_games = [];
        foreach ($games as $game) {
if ($game->LaunchId == 'football_mobile_html') {continue;}
            if($game->IsMobile && $game->Brand == self::BRAND_NETENT && !$this->isGameExcluded($game)) {
                $mobile_games[] = $this->correctGameInfo($game, 'netent', 'mobile');
            }
        }
        return $mobile_games;
    }

    private function getDesktopEGTGames() {
        $games = $this->getGamesList();
        $desktop_games = [];
        foreach ($games as $game) {
            if($game->IsDesktop && $game->Brand == self::BRAND_EGT && !$this->isGameExcluded($game)) {
                $desktop_games[] = $this->correctGameInfo($game, 'egt', 'desktop');
            }
        }
        return $desktop_games;
    }

    private function getMobileEGTGames() {
        $games = $this->getGamesList();
        $mobile_games = [];
        foreach ($games as $game) {
            if($game->IsMobile && $game->Brand == self::BRAND_EGT && !$this->isGameExcluded($game)) {
                $mobile_games[] = $this->correctGameInfo($game, 'egt', 'mobile');
            }
        }
        return $mobile_games;
    }

    private function getDesktopNovomaticGames() {
        $games = $this->getGamesList();
        $desktop_games = [];
        foreach ($games as $game) {
            if($game->IsDesktop && $game->Brand == self::BRAND_NOVOMATIC && !$this->isGameExcluded($game)) {
                $desktop_games[] = $this->correctGameInfo($game, 'novomatic', 'desktop');
            }
        }
        return $desktop_games;
    }

    private function getMobileNovomaticGames() {
        $games = $this->getGamesList();
        $mobile_games = [];
        foreach ($games as $game) {
            if($game->IsMobile && $game->Brand == self::BRAND_NOVOMATIC && !$this->isGameExcluded($game)) {
                $mobile_games[] = $this->correctGameInfo($game, 'novomatic', 'mobile');
            }
        }
        return $mobile_games;
    }

    private function getDesktopAmaticGames() {
        $games = $this->getGamesList();
        $desktop_games = [];
        foreach ($games as $game) {
            if($game->IsDesktop && $game->Brand == self::BRAND_AMATIC && !$this->isGameExcluded($game)) {
                $desktop_games[] = $this->correctGameInfo($game, 'amatic', 'desktop');
            }
        }
        return $desktop_games;
    }

    private function getMobileAmaticGames() {
        $games = $this->getGamesList();
        $mobile_games = [];
        foreach ($games as $game) {
            if($game->IsMobile && $game->Brand == self::BRAND_AMATIC && !$this->isGameExcluded($game)) {
                $mobile_games[] = $this->correctGameInfo($game, 'amatic', 'mobile');
            }
        }
        return $mobile_games;
    }

    /**
     * @param \stdClass $game
     * @return \stdClass
     */
    private function correctGameInfo(\stdClass $game, $provider, $device_type) {
        $image_url = Platform::singleton()->config->bet_advanced_config->image_url;
        $game_object = new \stdClass();
        $game_object->name = $game->Name;
        $game_object->launch_id = $game->LaunchId;
        $game_object->game_id = $game->Id;
        $game_object->brand = $game->Brand;
        $game_object->image = $image_url . $device_type . '/' . $provider . '/' . $game->LaunchId . '.jpg';

        return $game_object;
    }

    private function openGameSession() {
        $api_url = Platform::singleton()->config->bet_advanced_config->open_game_session_url;
        $current_user = Platform::singleton()->current_user;
        $params = [
            'operatorId'    => Platform::singleton()->config->bet_advanced_config->operator_id,
            'operatorKey'   => Platform::singleton()->config->bet_advanced_config->operator_key,
            'userId'        => $current_user->id,
            'userName'      => $current_user->username,
            'currency'      => $current_user->getUserCurrency()
        ];

        $result = self::getDataByCurl($api_url, $params);
        return $result;
    }

    private function getGameLink(\stdClass $parameters) {
        $gs_param = $this->getGSParam($parameters);
        $session = $this->openGameSession();
        $open_game_url = $this->getOpenGameUrlByBrand($parameters);
//todo close url
        $game_link_params = [
            'gameid' => $parameters->launch_id,
            'sessid' => $session->Values->SessionId,
            'lang' => self::getLanguage(),
        ];

        if($gs_param) {
            $game_link_params['gs'] = $gs_param;
        }

        $game_link = $open_game_url . '?';
        $count = 0;
        foreach ($game_link_params as $key => $param) {
            if($count > 0) {
                $game_link .= '&';
            }
            $game_link .=  $key . '=' . $param;
            $count++;
        }

        return $game_link;
    }

    private static function getLanguage() {
        $language = 'tr';
        switch ($_COOKIE['language']) {
            case 'tr_TR':
                $language = 'tr';
                break;
            case 'en_US':
                $language = 'en';
                break;
            case 'ru_RU':
                $language = 'ru';
                break;
        }

        return $language;
    }

    private function getGSParam($parameters) {
        switch ($parameters->brand) {
            case self::BRAND_NETENT:
                if (Platform::singleton()->current_user && SecurityManager::checkPermission('netent', 'action')) {
                    echo json_encode(Platform::singleton()->createAnswer(false, _('You don\'t have permission for netent bets')));
                    exit;
                }
                return Platform::singleton()->config->bet_advanced_config->gs_param_netent;
                break;
            case self::BRAND_AMATIC:
                if (Platform::singleton()->current_user && SecurityManager::checkPermission('amatic', 'action')) {
                    echo json_encode(Platform::singleton()->createAnswer(false, _('You don\'t have permission for amatic bets')));
                    exit;
                }
                return Platform::singleton()->config->bet_advanced_config->gs_param_amatic;
                break;
            case self::BRAND_NOVOMATIC:
                if($parameters->is_mobile) {
                    return Platform::singleton()->config->bet_advanced_config->gs_param_novomatic_mobile;
                }
                return Platform::singleton()->config->bet_advanced_config->gs_param_novomatic;
                break;
            case self::BRAND_EGT:
                if (Platform::singleton()->current_user && SecurityManager::checkPermission('egt', 'action')) {
                    echo json_encode(Platform::singleton()->createAnswer(false, _('You don\'t have permission for bets')));
                    exit;
                }
                if($parameters->is_mobile) {
                    return Platform::singleton()->config->bet_advanced_config->gs_param_egt_mobile;
                }
                return Platform::singleton()->config->bet_advanced_config->gs_param_egt;
                break;
            default:
                return '';
        }
    }

    private function getOpenGameUrlByBrand($parameters) {
        switch ($parameters->brand) {
            case self::BRAND_NETENT:
                return Platform::singleton()->config->bet_advanced_config->launch_netent_url;
                break;
            case self::BRAND_AMATIC:
                return Platform::singleton()->config->bet_advanced_config->launch_amatic_url;
                break;
            case self::BRAND_NOVOMATIC:
                if($parameters->is_mobile) {
                    return Platform::singleton()->config->bet_advanced_config->launch_novomatic_mobile_url;
                }
                return Platform::singleton()->config->bet_advanced_config->launch_novomatic_url;
                break;
            case self::BRAND_EGT:
                if($parameters->is_mobile) {
                    return Platform::singleton()->config->bet_advanced_config->launch_egt_mobile_url;
                }
                return Platform::singleton()->config->bet_advanced_config->launch_egt_url;
                break;
            default:
                return '';
        }
    }

    private function getBalance(\stdClass $parameters) {
        $this->checkAccess($parameters);
        $player = Player::selectById($parameters->userid);
        $wallet = User::getRealWallet($player);

        $response = [
            'error' => 0,
            'error_message' => null,
            'balance' => $wallet->balance->amount,
        ];
        return json_encode($response);
    }

    private function writeBet(\stdClass $parameters) {
        $this->checkAccess($parameters);

        if($parameters->type == self::TYPE_BET) {
            return $this->makeBet($parameters);
        } elseif($parameters->type == self::TYPE_WIN) {
            return $this->processWin($parameters);
        }
        return '';
    }

    private function makeBet(\stdClass $parameters) {
        $player = Player::selectById($parameters->userid);
        if(!SecurityManager::checkPlayerStatusForBets($player)) {
            return '';
        }
        $wallet = User::getRealWallet($player);
        $bet_amount = new Funds(abs($parameters->amount), $parameters->currency);

        $transaction = PaymentGateway::getInstance()->bet($wallet, $bet_amount, $player->id, $parameters->gameid, $parameters->brandid);

        $response = [
            'error' => 0,
            'error_message' => null,
            'balance' => $wallet->balance->amount,
            'transaction' => $transaction->id
        ];
        return json_encode($response);
    }

    private function processWin(\stdClass $parameters) {
        $player = Player::selectById($parameters->userid);
        $wallet = User::getRealWallet($player);
        $win_amount = new Funds(abs($parameters->amount), $parameters->currency);

        $transaction = PaymentGateway::getInstance()->win($wallet, $win_amount, $player->id, $parameters->gameid, $parameters->brandid);

        $response = [
            'error' => 0,
            'error_message' => null,
            'balance' => $wallet->balance->amount,
            'transaction' => $transaction->id
        ];
        return json_encode($response);
    }

    private static function getDataByCurl($url, $params) {
        $headers = [
            'Accept-Charset:UTF-8',
            'Accept: application/json',
            'Accept-Encoding: gzip,deflate,sdchrn'
        ];

        $url = $url . '?' . http_build_query($params);

        $result = HttpTools::GetDataByCurl($url, null, null, null, null, $headers);
        Tools::debugLog('bet_advanced_adapter', print_r([
            'url' => $url,
            'result' => $result,
        ], 1), true);
        $result = json_decode($result);
        return $result;
    }

    /**
     * @return array
     */
    public function getGamesList() {
        $params = [
            'operatorId' => Platform::singleton()->config->bet_advanced_config->operator_id,
            'operatorKey' => Platform::singleton()->config->bet_advanced_config->operator_key
        ];
        $get_games_url = Platform::singleton()->config->bet_advanced_config->get_games_url;
        $result = self::getDataByCurl($get_games_url, $params);
        return $result;
    }

    private function checkAccess($request) {
        $private_key = Platform::singleton()->config->bet_advanced_config->private_key;

        if($request->private_key != $private_key) {
            throw new BetAdvancedException('Access denied');
        }
    }

    public function getFrontGamesList(string $provider_type, bool $is_mobile = false) {
        $device_type = $is_mobile ? 'mobile' : 'desktop';

        $type = str_replace("bet_advanced_", '', $provider_type);

        $games = $this->processRequest("get_{$type}_{$device_type}_games");

        $front_games = [];

        foreach ($games as $game) {
            $front_game         = new FrontGame($game->launch_id, $game->name, $provider_type, $game->image);
            $front_game->params = [
                'brand'   => $game->brand,
                'game_id' => $game->game_id,
            ];
			$front_game->group_name = "All";
            $front_games[] = $front_game;
        }

        return $front_games;
    }
}
