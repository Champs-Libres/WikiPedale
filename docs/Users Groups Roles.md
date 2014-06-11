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

**Report**
   statuses 1 -----> *   **ReportStatus**
                          value (int)                   
                          type   * ------------>  1 **Notation**
                                                      id (string)

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
- a type: either _NOTATION_, _MODERATOR_ or _MANAGER_

Those three last parameters (zone, notation, type) are described below.

```

**User**
   groups * <------->  * **Groups**
                          zone    -----------------> **Zone**
                          notation  ---------------> **Notation**
                          type: NOTATION | MODERATOR | MANAGER

```

Group's Zone
------------

Members of the groups may only have an action on reports which are located within their zone.

Group's types
--------------

**Big picture**

- _NOTATION_ : allow members to manipulate notation on reports; 
- _MODERATOR_ : allow members to moderate reports: fill in moderator's comment, assign a manager, see details of report's creator (phonenumber, email,...), update details (lat-lon position, street name, description, ...) ... AND manipulate the corresponding notation.
- _MANAGER_ : allow members to see details of report's creator (phonenumber, email, ...), and change the corresponding notation.

**In detail**

Currently, the role are given in Progracqteur/WikiPedaleBundle/Entity/Management/Group::setType and security.yml (under the key 'security.role_hierarchy'). 

They are described in Progracqteur/WikipedaleBundle/Entity/Management/User.

Group's Notation
-----------------

When a Group's member want to update/create a notation, the notation he want to update/create MUST be attached to his group.




