.. _ChangedBackendAccessControl:

=============
Changed backend access control
=============

.. include:: ../../Includes.txt

Earlier versions of Calendar Base had their own configuration option
in User TSConfig to limit a backend users to seeing records from
certain PIDs, but other mounts were not respected.

|img-5| This has been changed in version 1.4 to follow Typo3's default access
control.  *This means that you might need to grant user and groups the
access to the storage folders explicitly.*


