.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.txt


.. _ts-richSnippetFields:

~.richSnippetFields
===================
      
This extension provides support for `Google rich snippets`_. 
Setting the following options some meta information could be configured which is being read from the database for each rated item.
The configured options must be field descriptors in the rated table. 

.. container:: ts-properties

   ==================================== ============================================= ===============
   Property                             Title                                         Type
   ==================================== ============================================= ===============
   name_                                Fieldname to fetch the item name from         string
   description_                         Fieldname to fetch the item description from  string
   aggregateRatingSchemaType_           SchemaType having aggregateRating as property string
   ==================================== ============================================= ===============

   :ts:`[tsref:plugin.tx_thrating.settings.richSnippetFields]`


.. _rsfName:

name
^^^^

.. container:: table-row

   Property
      name

   Data type
      :ref:`t3tsref:data-type-string`

   Description
      Fieldname to fetch the item name from.
      If no or an invalid name is given, the default value consists of the constant text "Rating AX" 
      appendixed by the UID values of the ratingobject and the rated object.
      (e.g. "``Rating AX 2_30``" meaning ratingobject #2 / ratedobject #30).

   Default
      :ts:`Rating AX xx_yy`

.. _rsfDescription:

description
^^^^^^^^^^^

.. container:: table-row

   Property
      description

   Data type
      :ref:`t3tsref:data-type-string`

   Description
      Fieldname to fetch the item description from 

   Default
      \       
      
      
Example
-------

.. code-block:: typoscript
   :linenos:

   temp.pollingDemo < plugin.tx_thrating
   temp.pollingDemo {
      settings {
         richSnippetFields {
            name = pollingheader
            description = pollingbodytext
            url =
         }
      }
   }
   

.. _rsfAggregateRatingSchemaType:

aggregateRatingSchemaType
^^^^^^^^^^^^^^^^^^^^^^^^^

.. container:: table-row

   Property
      aggregateRatingSchemaType

   Data type
      :ref:`t3tsref:data-type-string`

   Description
      According to `Schema.org aggregateRating`_ valid entries are:
         - ``Brand``
         - ``CreativeWork``
         - ``Event``
         - ``Offer``
         - ``Organization``
         - ``Place``
         - ``Product``
         - ``Service``

      .. warning::
      
         Any other value will cause an exception during frontend rendering.

   Default
      :ts:`Product`
