<?php
interface IAuthProvider
{
    public function isAuthed();
	public function doAuth();
    public function getAuthLocation();
	public function deAuth();
}
?>