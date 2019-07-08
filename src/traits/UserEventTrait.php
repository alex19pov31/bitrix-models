<?php

namespace Alex19pov31\BitrixModel\Traits;

use Bitrix\Main\EventManager;

trait UserEventTrait
{
    public static function listenEvents()
    {
        if (!static::$em) {
            static::$em = EventManager::getInstance();
        }

        static::$em->addEventHandler('main', 'OnBeforeUserRegister', [
            static::class,
            'onBeforeUserRegisterHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserRegister', [
            static::class,
            'onAfterUserRegisterHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserSimpleRegister', [
            static::class,
            'onBeforeUserSimpleRegisterHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserSimpleRegister', [
            static::class,
            'onAfterUserSimpleRegisterHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserLogin', [
            static::class,
            'onBeforeUserLoginHandler',
        ]);
        static::$em->addEventHandler('main', 'OnUserLoginExternal', [
            static::class,
            'onUserLoginExternalHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserLogin', [
            static::class,
            'onAfterUserLoginHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserLoginByHash', [
            static::class,
            'onBeforeUserLoginByHashHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserLoginByHash', [
            static::class,
            'onAfterUserLoginByHashHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserAuthorize', [
            static::class,
            'onAfterUserAuthorizeHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserLogout', [
            static::class,
            'onBeforeUserLogoutHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserLogout', [
            static::class,
            'onAfterUserLogoutHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserAdd', [
            static::class,
            'onBeforeUserAddHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserAdd', [
            static::class,
            'onAfterUserAddHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserUpdate', [
            static::class,
            'onBeforeUserUpdateHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAfterUserUpdate', [
            static::class,
            'onAfterUserUpdateHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserDelete', [
            static::class,
            'onBeforeUserDeleteHandler',
        ]);
        static::$em->addEventHandler('main', 'OnUserDelete', [
            static::class,
            'onUserDeleteHandler',
        ]);
        static::$em->addEventHandler('main', 'OnExternalAuthList', [
            static::class,
            'onExternalAuthListHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserChangePassword', [
            static::class,
            'onBeforeUserChangePasswordHandler',
        ]);
        static::$em->addEventHandler('main', 'OnBeforeUserSendPassword', [
            static::class,
            'onBeforeUserSendPasswordHandler',
        ]);
        static::$em->addEventHandler('main', 'OnUserLogin', [
            static::class,
            'onUserLoginHandler',
        ]);
        static::$em->addEventHandler('main', 'OnUserLogout', [
            static::class,
            'onUserLogoutHandler',
        ]);
        static::$em->addEventHandler('main', 'OnSendUserInfo', [
            static::class,
            'onSendUserInfoHandler',
        ]);
        static::$em->addEventHandler('main', 'OnAuthProvidersBuildList', [
            static::class,
            'onAuthProvidersBuildListHandler',
        ]);
    }

    public static function onBeforeUserRegisterHandler(&$arFields)
    {}
    public static function onAfterUserRegisterHandler(&$arFields)
    {}
    public static function onBeforeUserSimpleRegisterHandler(&$arFields)
    {}
    public static function onAfterUserSimpleRegisterHandler(&$arFields)
    {}
    public static function onBeforeUserLoginHandler(&$arFields)
    {}
    public static function onUserLoginExternalHandler(&$arFields)
    {}
    public static function onAfterUserLoginHandler(&$arFields)
    {}
    public static function onBeforeUserLoginByHashHandler(&$arFields)
    {}
    public static function onAfterUserLoginByHashHandler(&$arFields)
    {}
    public static function onAfterUserAuthorizeHandler(&$arFields)
    {}
    public static function onBeforeUserLogoutHandler(&$arFields)
    {}
    public static function onAfterUserLogoutHandler(&$arFields)
    {}
    public static function onBeforeUserAddHandler(&$arFields)
    {}
    public static function onAfterUserAddHandler(&$arFields)
    {}
    public static function onBeforeUserUpdateHandler(&$arFields)
    {}
    public static function onAfterUserUpdateHandler(&$arFields)
    {}
    public static function onBeforeUserDeleteHandler($userId)
    {}
    public static function onUserDeleteHandler($userId)
    {}
    public static function onExternalAuthListHandler($id, $name)
    {}
    public static function onBeforeUserChangePasswordHandler(&$arFields)
    {}
    public static function onBeforeUserSendPasswordHandler(&$arFields)
    {}
    public static function onUserLoginHandler(&$arFields)
    {}
    public static function onUserLogoutHandler(&$arFields)
    {}
    public static function onSendUserInfoHandler(&$arFields)
    {}
    public static function onAuthProvidersBuildListHandler(&$arFields)
    {}

}
