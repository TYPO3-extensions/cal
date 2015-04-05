.. include:: Images.txt
.. _TheAddressListExtension:

===========================
The Address List Extension
===========================

.. include:: ../../Includes.txt

The Address List extension gives you the opportunity to use a centralized address database for organizer and location records. The advantage of using this extension is that it is provides a global repository for address records and can be accessed by a number of other extensions.

Install the extension like any typical extension. Once you've updated the databases, click on the Calendar Base extension in the Extension Manager Module and open the Configuration form. Refer to Illustration 34. If you want to use the Address List extension for locations, select the tt\_address option from the configuration form.

|img-41|
**Illustration 34: Selecting the Address Extension in the Configuration Form**

Select the cal location data model* drop-down menu ( **1** ). If you want to use the Address List extension for event organizers, select the tt\_address option from the  ***Select the cal organizer data model*** drop-down menu ( **2** ). Then update the data base.Next, you will need to add the Addresses (tt\_address) static template either to the root template or the Calendar Base template if you've created one.

Finally, you will need to create an Address Group and Address records in the Backend. We recommend these records be created in your site's global Storage Folder. See the tt\_address Manual for further instructions.

|img-42|
**Illustration 35: Address List Record From**

|img-4| **Note:** If you are using a Calendar Base Storage folder, you will need to add the global Storage Folder to the Starting Point field in the Calendar Base Flexform in order to access Address records.

|img-4| **Note:** Refer to Illustration 35. To activate an Address record in Calendar Base extension, you will need to select the corresponding check-boxes at the bottom of the the Address record form.


