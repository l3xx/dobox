<?php

interface ICacheDependency
{
	public function evaluateDependency();
	public function getHasChanged();
}
