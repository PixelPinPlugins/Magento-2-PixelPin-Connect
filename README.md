# PixelPin Connect.

Magento 2 Extension for authenticating with [PixelPin](http://pixelpin.co.uk) using the OAuth 2.0 API.

The PixelPin Connect Extension allows your users to authenticate using PixelPin on your Magento 2 Websites.

Connect your store to PixelPin and let users authenticate using passwordless pictures. With PixelPin Connect extension your clients log in to your store using their PixelPin pictures. There is no need to complete numerous forms for a successful registration. 

[Create your PixelPin developer account here](https://login.pixelpin.co.uk).

## Key Features:

- Built in optional Two-Factor.

- PixelPin is passwordless.

- Your customers can Sign In, Create An Account and Checkout Using PixelPin.

- PixelPin Connect extension is available for free!

- Open Source

## Installation

Download the PixelPin Connect Extension

Go to the downloaded PixelPin Connect Extension folder and copy these three files:

![alt tag](https://s13.postimg.org/rh29r9uef/install__1.png)

And paste them into your Magento 2 website root directory 

![alt tag](https://s13.postimg.org/iajz3zp5z/install_2.png)

Then in your CLI type this command from your Magento 2 website root directory:

**'php bin/magento setup:upgrade'**

then type this command:

**'php bin/magento setup:static-content:deploy'**

## Enabling PixelPin Connect

### Configure Developer Client ID and App Secret

To obtain a PixelPin Client ID and App Secret you'll need to create a PixelPin Account then create a Developer Account on PixelPin which you can do [here](https://login.pixelpin.co.uk)

#### Setup PixelPin on Your Magento 2 Website

First go to your Magento 2 website admin dashboard and click on "PixelPin Connect" on the menu sidebar.

![alt tag](https://s14.postimg.org/874wintu9/step1.png)

Click on Settings in the PixelPin Connect sub-menu

![alt tag](https://s14.postimg.org/5eboymtht/step2.png)

Then set enable to yes, then enter your PixelPin developer account Client ID and Client Secret. Instructions to get your Client ID and Client Secret can be found below. Remember to save by pressing "Save Config". You may need to refresh cache types.

![alt tag](https://s21.postimg.org/lf924parr/step3.png)

#### Step-by-step of how to create a PixelPin developer account

First [Sign Into PixelPin](https://login.pixelpin.co.uk)

Click 'More'

![alt tag](https://s20.postimg.org/c6lq7jwzh/pp1.png)

Click 'Add/Edit Developer Accounts'

![alt tag](https://s20.postimg.org/jnuxmrmil/pp2.png)

Click 'Create Account'

![alt tag](https://s20.postimg.org/edpyvh29p/pp3.png)

Fill in your website details, your Redirect URI can be found in PixelPin Connect Settings on your Magento 2 Admin dashboard. Click create once you're done.

![alt tag](https://s20.postimg.org/dcpq6cla5/pp4.png)

Validate your developer account via the email you just entered

![alt tag](https://s20.postimg.org/ds0zzd77h/ppconfirm.png)

Then you should see your Client ID and Client Secret

![alt tag](https://s20.postimg.org/ljhpxxbct/pp5.png)

#### Installation is now finished

Your Magento website's login page should look something like this:

![alt tag](https://s20.postimg.org/rt7w09ry5/done.png)

## Credits

  - Magento 2 Conversion Author: [Callum Brankin](https://github.com/CallumBrankin)
  - Original Magento 1 Extension Author: [Marko Martinović](https://github.com/Marko-M)

## License

[OSL-3.0](https://opensource.org/licenses/OSL-3.0)