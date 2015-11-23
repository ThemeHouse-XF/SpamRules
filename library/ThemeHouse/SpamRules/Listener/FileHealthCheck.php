<?php

class ThemeHouse_SpamRules_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/SpamRules/ControllerAdmin/SpamRule.php' => '239aaaf7803d4ef395132c71fcd056c9',
                'library/ThemeHouse/SpamRules/ControllerAdmin/UserSpamRule.php' => '21f5b88aeb429b20aa3b3a59f4a5612d',
                'library/ThemeHouse/SpamRules/DataWriter/SpamRule.php' => '7c0cfc7b41e5a329298fd117f3cdb559',
                'library/ThemeHouse/SpamRules/DataWriter/UserSpamRule.php' => '04f2ec91a70b80e5ca3cd852db8c1f6a',
                'library/ThemeHouse/SpamRules/Extend/XenForo/Model/SpamPrevention.php' => '0e3e019f3dc04e614b56f3aa63f1a5fa',
                'library/ThemeHouse/SpamRules/Helper/Criteria.php' => 'd963ac6f7054fe54c440b124c4bea326',
                'library/ThemeHouse/SpamRules/Install/Controller.php' => 'ee7a4fb79333dcb422225bff6a036ab4',
                'library/ThemeHouse/SpamRules/Listener/LoadClass.php' => 'a47f443c68b1f635fadec5670182ad0e',
                'library/ThemeHouse/SpamRules/Model/SpamRule.php' => 'f90a9529fa71e7cc0fabb26c58c974a3',
                'library/ThemeHouse/SpamRules/Model/UserSpamRule.php' => 'e4500032e007d65b0b61d8500020a1c2',
                'library/ThemeHouse/SpamRules/Route/PrefixAdmin/SpamRules.php' => '458a41cd7c7192776a197763c3a94514',
                'library/ThemeHouse/SpamRules/Route/PrefixAdmin/UserSpamRules.php' => '8cb59f159aed27f82eb6390f1556e2b2',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
            ));
    }
}