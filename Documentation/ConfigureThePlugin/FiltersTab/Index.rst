.. include:: Images.txt
.. _FiltersTab:

=============
Filters Tab
=============

.. include:: ../../Includes.txt


|img-17|
**Illustration 10: Plugin Configuration: Filters Tab**

Refer to Illustration 10. If you have created multiple calendars and/or categories, the Filters Tab allows you to limit which ones are displayed in the Frontend.
- **Calendar Selection Mode:** Determines whether TYPO3 limits the options based on selected items or not.

- **Show All (don't care about selection below):** TYPO3 will ignore any
  selected items.

- **Show only selected calendars:** Limits the display to only those
  selected items.

- **Don't show selected calendars:** Shows all items *except* those that
  have been selected.

- **Category Selection Mode:** Determines whether TYPO3 limits the
  options based on selected items or not.

- **Show All (don't care about selection below):** TYPO3 will ignore any
  selected items.

- **Show only selected categories (exact match):** Limits the display to
  only those selected items.

- **Don't show selected categories:** Shows all items *except* those that
  have been selected.

- **Show only selected categories (any):** Shows any events that have at
  least one of the selected items assigned.

|img-4| **Note:** Starting with version 1.5, category filtering is now
more strict in case that an event has more than one category assigned.
The intention is to make sure that

- no events are shown if their category is not explicitly allowed

- no events are shown if their category is explicitly disallowed

Previous version were not that strict: Events were shown if an event
had at least one allowed category assigned.

*Example:* Your event E1 has categories “C1” and “C2” assigned, event
E2 has “C1”

================================    ===============    =====================
Category filter mode                Category filter    Result
================================    ===============    =====================
Show only selected (exact match)    C1                 E1 is not shown.
                                                       E2 is shown.
Show only selected (exact match)    C1, C2             E1 and E2 are shown.
Show only selected (exact match)    C2                 E1 is shown.
                                                       E2 is not shown.
Don't show selected...              C2                 E1 is not shown.
                                                       E2 is shown.
Don't show selected...              C1                 Nothing is shown.
================================    ===============    =====================
