<?php

namespace Api\Library\Shared\Script\Migration;

use Api\Model\Mapper\MapperListModel;
use Api\Model\Shared\Rights\SystemRoles;
use Api\Model\UserModel;
use Api\Model\UserListModel;
use Api\Model\UserModelMongoMapper;
use Api\Model\UserProfileModel;


(php_sapi_name() == 'cli') or exit('this script must be run on the command-line');

require_once '../scriptConfig.php';

class FixUsersMissingSiteRole
{
    public static function run($mode = 'test')
    {
        $testMode = ($mode != 'run');
        print("Remove users who have no siteRole AND never logged in AND are not system admins\n\n");

        $userlist = new AllUsersListModel();
        $userlist->read();

        /*
        $chris = new UserProfileModel('539fdd8756dd85c329df2ad6');
        var_dump($chris);
        $isEmpty = count($chris->siteRole);
        print "siteRoles: $isEmpty\n";
        print "empty(last_login) " . empty($chris->last_login) . "\n";
        print "role: " . $chris->role != SystemRoles::SYSTEM_ADMIN . "\n";
*/
        $usersMissingSiteRole = 0;
        foreach ($userlist->entries as $userParams) { // foreach existing user
            $user = new UserProfileModel($userParams['id']);
            if ($user->siteRole == '{}') {
                print "Found siteRole {}\n";
            }
            /*
            if ((!property_exists($user, 'siteRole') || empty($user->siteRole)) &&
                (!property_exists($user, 'last_login') || empty($lastLogin)) &&
                ($user->role != SystemRoles::SYSTEM_ADMIN)) {
            */

            if ((count($user->siteRole) == 0) && empty($user->last_login) && ($user->role != SystemRoles::SYSTEM_ADMIN)) {
                if (!$testMode) {
                    $user->remove();
                }
                $usersMissingSiteRole++;

                /*
                print "Removed [";
                if (!empty($user->username)) {
                    print "username: $user->username; ";
                }
                if (!empty($user->email)) {
                    print "email: $user->email; ";
                }
                if (!empty($user->emailPending)) {
                    print "emailPending: $user->emailPending; ";
                }
                if (!empty($user->last_login)) {
                    print "lastLogin: $user->last_login;";
                }
                if (!empty($user->role)) {
                    print "role: $user->role;";}
                print "]\n";
                */
            }
        }

        if ($usersMissingSiteRole > 0) {
            print "\n\nRemoved $usersMissingSiteRole users missing siteRole from the users collection\n\n";
        } else {
            print "\n\nNo non-existent user siteRoles found in the users collection\n\n";
        }
    }
}

/**
 * Class AllUsersListModel
 * UserListModel ignores users with null usernames, we use this to return all users from the users collection.
 */
class AllUsersListModel extends UserListModel
{
    public function __construct()
    {
        MapperListModel::__construct(
            UserModelMongoMapper::instance(),
            array(),
            array('username', 'id', 'email', 'name', 'emailPending', 'role', 'siteRole')
        );
    }

}


FixUsersMissingSiteRole::run('test');
