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
        // TODO: Add "AND never logged in" criteria DDW 2016-09
        print("Remove users who have no siteRole AND are not system admins\n\n");

        $userlist = new CustomUserListModel();
        $userlist->read();

        print "Found " . count($userlist->entries) . " users in the userlist\n";

        $usersMissingSiteRole = 0;
        foreach ($userlist->entries as $userParams) { // foreach user from the custom list
            // if last_login has any valid meaning, we need UserProfileModel.  Otherwise, can use UserModel
            $user = new UserProfileModel($userParams['id']);

            if (($user->role != SystemRoles::SYSTEM_ADMIN) &&
                (count(array_keys($user->siteRole->getArrayCopy())) == 0)
            ) {
                if (!$testMode) {
                    $user->remove();
                }
                $usersMissingSiteRole++;

                print "Removed [id: $user->id ";

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
                    print "role: $user->role;";
                }
                print "]\n";
            }
        }

        if ($usersMissingSiteRole > 0) {
            print "\n\nRemoved $usersMissingSiteRole users missing siteRole from the users collection\n\n";
        } else {
            print "\n\nNo user missing siteRoles found in the users collection\n\n";
        }
    }
}

/**
 * Class CustomUserListModel
 * UserListModel ignores users with null username, so we use this derived class to query our custom user list.
 */
class CustomUserListModel extends UserListModel
{
    public function __construct()
    {
        MapperListModel::__construct(
            UserModelMongoMapper::instance(),
            array(),
            array('username', 'id', 'email', 'name', 'emailPending', 'last_login', 'role', 'siteRole')
        );
    }

}


FixUsersMissingSiteRole::run('test');
