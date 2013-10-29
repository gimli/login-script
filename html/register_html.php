<?php
 
// Need this little bit of PHP to show errors they encounter
if (isset($_SESSION['errors'])) {
    echo $_SESSION['errors'];

    unset($_SESSION['errors']);
}
?>
<form action="register-actions.php" method="post">
    <input type="hidden" name="process" value="true" />
    <table border="0">
        <tr>
            <td>Username</td>
            <td>
                <input type="text" name="username" maxlength="30" />
            </td>
        </tr>
        <tr>
            <td>Password:</td>
            <td>
                <input type="password" name="password" maxlength="30" />
            </td>
        </tr>
        <tr>
            <td>Confirm:</td>
            <td>
                <input type="password" name="passwordConfirm" maxlength="30" />
            </td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>
                <input type="text" name="email" maxlength="255" />
            </td>
        </tr>
        <tr>
            <td>Confirm:</td>
            <td>
                <input type="text" name="emailConfirm" maxlength="255" />
            </td>
        </tr>
        <tr>
           <td colspan="2">
               <input type="submit" value="Register" />
           </td>
        </tr>
    </table>
</form>
