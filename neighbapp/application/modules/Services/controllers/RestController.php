<?php
class Services_RestController extends Cfe_Controller_Rest
{
    protected function getFormat() {
        return Cfe_Rest_Server::FORMAT_JSON;
    }
}