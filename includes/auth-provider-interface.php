<?php
interface IAuthProvider
{
    public function isAuthed();
    public function getAuthLocation();
	public function deAuth();
}
?>