<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['jobseeker_id'])){
    echo json_encode(['success'=>false,'message'=>'Chưa đăng nhập']);
    exit;
}

$jobseeker_id = (int)$_SESSION['jobseeker_id'];

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['cv_file'])){
    $file = $_FILES['cv_file'];
    $allowedExt = ['doc','docx','pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if(!in_array($ext,$allowedExt)){
        echo json_encode(['success'=>false,'message'=>'Định dạng file không hợp lệ']);
        exit;
    }

    if($file['size'] > 5*1024*1024){
        echo json_encode(['success'=>false,'message'=>'File quá lớn (max 5MB)']);
        exit;
    }

    $uploadDir = 'uploads/cv_file/';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $newFileName = $jobseeker_id.'_'.time().'.'.$ext;
    $targetPath = $uploadDir.$newFileName;

    if(move_uploaded_file($file['tmp_name'],$targetPath)){
        $stmt = $conn->prepare("SELECT id FROM jobseeker_profile WHERE jobseeker_id=?");
        $stmt->bind_param("i",$jobseeker_id);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows>0){
            $stmt = $conn->prepare("UPDATE jobseeker_profile SET cv_file=?, updated_at=NOW() WHERE jobseeker_id=?");
            $stmt->bind_param("si",$targetPath,$jobseeker_id);
        }else{
            $stmt = $conn->prepare("INSERT INTO jobseeker_profile(jobseeker_id,cv_file) VALUES(?,?)");
            $stmt->bind_param("is",$jobseeker_id,$targetPath);
        }

        if($stmt->execute()){
            echo json_encode(['success'=>true,'file_name'=>$file['name']]);
        }else{
            echo json_encode(['success'=>false,'message'=>$stmt->error]);
        }
    }else{
        echo json_encode(['success'=>false,'message'=>'Upload file thất bại']);
    }
}
