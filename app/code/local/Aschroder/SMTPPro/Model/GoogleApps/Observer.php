<?php
/**
 * @author Ashley Schroder (aschroder.com)
 * @copyright  Copyright (c) 2010 Ashley Schroder
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Aschroder_SMTPPro_Model_GoogleApps_Observer extends Varien_Object
{
	

    /**
     * Expects observer with:
     * 'mail', the mail about to be sent
     * 'template', template model for email being sent
     * 'email', the email object initiating the send
     * 'transport', an initially empty transport Object, will be used if set.
     *
     * @param $observer
     */
    public function beforeSend($observer) 
    {
        $event = $observer->getEvent();
        $storeId = $event->getTemplate()->getStoreId();

        if(!Mage::helper('smtppro/config')->isGoogleAppsEnabled($storeId)) {
            return $this;
        }

        Mage::helper('smtppro/debug')->log("Running Google Apps sender code.");

        $email = explode(",", Mage::getStoreConfig('system/googlesettings/email', $storeId));

        /**
         * We now allow a load balance of multiple gmail accounts to get past the 500/day limit.
         * @todo make this do proper load balancing instead of just randomizing emails to use.
         */
        if (count($email)) {
            $email = $email[array_rand($email)];
        } else {
            Mage::helper('smtppro/debug')->log("No email configured - you need to specify one in the magento configuration, otherwise your connection will fail");
        }
        
        $password = Mage::getStoreConfig('system/googlesettings/gpassword', $storeId);
        
        Mage::helper('smtppro/debug')->log("Preparing the Google Apps/Gmail Email transport, email to send with is: {$email}.");

        $config = array(
            'ssl' => 'tls', 
            'port' => 587, 
            'auth' => 'login', 
            'username' => $email, 
            'password' => $password
        );

        $emailTransport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);

        $event->getTransport()->setEmailTransport($emailTransport);

        return $this;
    }

    /**
     * Expects observer with:
     * 'mail', the mail about to be sent
     * 'template', the template being used
     * 'variables', the variables used in the template
     * 'transport', an initially empty transport Object, will be used if set.
     * @todo this needs to be used after I update the Email.php model
     * @param $observer
     */
    // public function beforeSendTemplate($observer) 
    // {
    //     $e = $observer->getEvent();
    //     $storeId = $e->getTemplate()->getStoreId();

    //     $transport = new App_Mail_Transport_GoogleApps(
    //         array(
    //             'accessKey' => Mage::getStoreConfig('system/sessettings/aws_access_key', $storeId),
    //             'privateKey' => Mage::getStoreConfig('system/sessettings/aws_private_key', $storeId) 
    //         )
    //     );
        
    //     $transport = $e->getTransport();
    //     $transport->setTransport($transport);

    //     return $this;
    // }

	
}