<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Authorization adapter through Kaltura Users
 *
 * @author gonen
 */
class Kms_Auth_AuthZ_Kaltura implements Kms_Auth_Interface_AuthZ
{
    const ADAPTER_NAME = "Kaltura AuthZ";

    public function authorizeUser($userId)
    {
        $userModel = Kms_Resource_Models::getUser();
        if(!isset($userModel->user))
        {
            Kms_Log::log('user is not set on model. fetching from API', Kms_Log::INFO);
            try
            {
                $user = $userModel->get($userId);
            }
            catch(Kaltura_Client_Exception $ex)
            {
                // could not get user from Kaltura, i.e. user does not have role
                Kms_Log::log('could not get user from Kaltura, cannot authorize. '.$ex->getMessage(), Kms_Log::INFO);
                return false;
            }
        }
        else
        {
            $user = $userModel->user;
        }

        if($user->isAdmin)
        {
            // force highest role for admin users
            $role = Kms_Plugin_Access::getRole(Kms_Plugin_Access::UNMOD_ROLE);
        }
        else
        {
            $role = ($userModel->getRole())? $userModel->getRole(): $userModel->getKalturaUserRole($userId);
            if($role == Kms_Plugin_Access::EMPTY_ROLE)
            {
                if(isset($user->partnerData))
                {
                    // try to authorize against partner data in case user failed migration
                    Kms_Log::log('Trying to authorize old user via partnerData', Kms_Log::DEBUG);
                    $role = self::parseRole($user->partnerData);
                    if(!is_null($role) && Kms_Plugin_Access::roleExists($role))
                    {
                        // update user role in custom data - i.e. partial migration
                        $userModel->setKalturaUserRole($user->id, $role);
                        return $role;
                    }
                }
                Kms_Log::log('login: No role set for user '.$userId, Kms_Log::WARN);
                return false;
            }
        }

        return $role;
    }

    public static function parseRole($partnerData)
    {
        if(preg_match('/role=([^,]+)/', $partnerData, $matches) && $matches[1])
        {
            return $matches[1];
        }
        else
        {
            return null;
        }
    }
}

?>
