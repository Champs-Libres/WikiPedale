When the software WikiPedale was designed, the developpers took into account the possibility to mark report differently for different groups of users. For instance administrative autorities could say "this report is closed for us" while cyclist may say "for us, this is still opened and we are asking for new jobs".

In order to achieve this goal, the software offers the possibility to attach **notations** from different **types** to **reports**. The local authority may assign his own **notation** (i.e. _notation.type_ = 'city'), different for cyclists (i.e. _notation.type_ = 'cyclist').

**SHORT RECALL** The **notation** may have three states : 

- rejected (color: grey, value -1), not shown on the map ;
- not taken into account by the moderator (color: blank, value: 0) ;
- taken into account (color: red, value: 1)
- a solution is planned (color: orange, value: 2)
- resolved (color: green, value: 3)

**WARNING** : the possibility to offer different notation is available from the API, but is still not implemented in the UI. If there is not request for this feature, this may disappear later.

Reports 
=======

**Reports** are linked with one or more **ReportStatus**, which is linked to a **Notation**.

```

**Report**             **ReportStatus**       **Notation**
   statuses 1 -----> *    report                   
                          type   * ------------>  1 id (string)

```

(_NOTE_ : See also the file docs/Diagrams/class_diagram.dia)


Who may create / update notations on the report ?
--------------------------------------------------

You must have the right to manipulate the corresponding notation. 

This is allowed by the groups's belonging.

Users and Groups
=================

**Users** may belong to one or more groups.

**Groups** have three caracteristics :

- a name, used for the UI ;
- a zone : a polygon where the group may be active. This is mandatory.
- a notation : the members of the group will be allowed to manipulate notation on report.

```

**User**
   groups * <------->  * *Groups*
                          zone    -----------------> *Zone*
                          notation  ---------------> *Notation*

```
