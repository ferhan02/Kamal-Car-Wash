<?php


// =========================
// ADMIN PATH
// =========================


$admin_path = "";



?>





<style>


/* =========================
ADMIN TOOLBAR
========================= */


.admin-toolbar{


    position:fixed;


    top:140px;


    right:0;


    z-index:9999;


}





.admin-title{


    background:#2f5aa8;


    color:white;


    padding:12px 22px;


    font-weight:600;


    cursor:pointer;


    border-radius:10px 0 0 10px;


    box-shadow:

    0 5px 15px rgba(0,0,0,.25);


    display:flex;


    align-items:center;


    gap:8px;


    transition:.3s;


}





.admin-title::after{


    content:"▼";


    font-size:11px;


    transition:.3s;


}






.admin-toolbar:hover .admin-title{


    background:#1f3f7a;


    box-shadow:

    0 0 15px rgba(47,90,168,.8);


}






.admin-toolbar:hover .admin-title::after{


    transform:rotate(180deg);


}






.admin-menu{


    position:absolute;


    right:0;


    top:55px;


    width:240px;


    background:white;


    box-shadow:

    0 15px 35px rgba(0,0,0,.25);


    border-radius:12px;


    overflow:hidden;


    opacity:0;


    visibility:hidden;


    transform:


    translateY(-20px)

    scale(.95);


    transition:.35s ease;


}






.admin-toolbar:hover .admin-menu{


    opacity:1;


    visibility:visible;


    transform:


    translateY(0)

    scale(1);


}






.admin-menu a{


    display:block;


    padding:14px 18px;


    color:#1f2a44;


    text-decoration:none;


    font-size:14px;


    border-bottom:1px solid #eee;


    position:relative;


    transition:.25s;


}






.admin-menu a:hover{


    background:#eaf2fb;


    color:#2f5aa8;


    padding-left:28px;


}






.admin-menu a:hover::before{


    content:"";


    position:absolute;


    left:0;


    top:0;


    height:100%;


    width:5px;


    background:#2f5aa8;


}



</style>


<div class="admin-toolbar">


<div class="admin-title">

⚙ Admin Panel

</div>


<div class="admin-menu">

<a href="staff_management.php">

👥 Staff Management

</a>

<a href="leave_management.php">

📝 Leave Management

</a>

<a href="overtime_management.php">

⏱ Overtime Management

</a>

<a href="salary_advance_management.php">

💵 Salary Advance

</a>

</div>


</div>