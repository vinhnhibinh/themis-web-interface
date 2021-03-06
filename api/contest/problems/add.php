<?php
    //? |-----------------------------------------------------------------------------------------------|
    //? |  /api/contest/problems/add.php                                                                |
    //? |                                                                                               |
    //? |  Copyright (c) 2018-2020 Belikhun. All right reserved                                         |
    //? |  Licensed under the MIT License. See LICENSE in the project root for license information.     |
    //? |-----------------------------------------------------------------------------------------------|

	// SET PAGE TYPE
    define("PAGE_TYPE", "API");
    
    require_once $_SERVER["DOCUMENT_ROOT"] ."/lib/ratelimit.php";
    require_once $_SERVER["DOCUMENT_ROOT"] ."/lib/belibrary.php";
    require_once $_SERVER["DOCUMENT_ROOT"] ."/lib/logs.php";
    
    if (!isLoggedIn())
        stop(11, "Bạn chưa đăng nhập.", 401);
    
    checkToken();
    
    if ($_SESSION["id"] !== "admin")
        stop(31, "Access Denied!", 403);

    require_once $_SERVER["DOCUMENT_ROOT"] ."/data/problems/problem.php";

    $id = preg_replace("/[^a-zA-Z0-9]/m", "", reqForm("id"));
    $name = reqForm("name");

    $point = reqType(reqForm("point"), "integer");
    $time = reqType(getForm("time", 1), "integer");
    $memLimit = reqType(getForm("memory", 1024), "integer");
    $inpType = getForm("inpType", "Bàn Phím");
    $outType = getForm("outType", "Màn Hình");
    $accept = isset($_POST["acpt"]) ? json_decode($_POST["acpt"], true) : Array("pas", "cpp", "c", "pp", "exe", "class", "py", "java");
    $image = isset($_FILES["img"]) ? $_FILES["img"] : null;
    $attachment = isset($_FILES["attm"]) ? $_FILES["attm"] : null;
    $description = reqForm("desc");
    $test = isset($_POST["test"]) ? json_decode($_POST["test"], true) : Array();
    $disabled = withType(getForm("disabled"), "boolean", false);

    $code = problemAdd($id, Array(
        "name" => $name,
        "point" => $point,
        "time" => $time,
        "memory" => $memLimit,
        "type" => Array(
            "inp" => $inpType,
            "out" => $outType
        ),
        "accept" => $accept,
        "description" => $description,
        "test" => $test,
        "disabled" => $disabled
    ), $image, $attachment);

    switch ($code) {
        case PROBLEM_OKAY:
            writeLog("OKAY", "Đã thêm đề \"$id\"");
            stop(0, "Thêm đề thành công!", 200, Array( "id" => $id ));
            break;
        case PROBLEM_ERROR_IDREJECT:
            stop(45, "Đã có đề với id này!", 404, Array( "id" => $id ));
            break;
        case PROBLEM_ERROR_FILEREJECT:
            stop(43, "Không chấp nhận loại tệp!", 400, Array( "id" => $id, "allow" => IMAGE_ALLOW ));
            break;
        case PROBLEM_ERROR_FILETOOLARGE:
            stop(42, "Tệp quá lớn!", 400, Array( "id" => $id, "image" => MAX_IMAGE_SIZE, "attachment" => MAX_ATTACHMENT_SIZE ));
            break;
        case PROBLEM_ERROR:
            stop(-1, "Lỗi không rõ.", 500, Array( "id" => $id ));
            break;
    }