<?php
namespace SimpleFramework\Http;

interface IController {
    public function index($req, $res);
    public function show($req, $res);
    public function store($req, $res);
    public function update($req, $res);
    public function destroy($req, $res);
}

