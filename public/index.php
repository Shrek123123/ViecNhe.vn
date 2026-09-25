<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tạo Link thanh toán</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body style="display: flex">
    <div style="padding-top: 10px; display: flex; flex-direction: column">
      <div
        style="border: 2px solid blue; border-radius: 10px; overflow: hidden"
      >
        <div id="content-container" style="padding: 10px">
          <p><strong>Tên sản phẩm:</strong> Mì tôm Hảo Hảo ly</p>
          <p><strong>Giá tiền:</strong> 2000 VNĐ</p>
          <p><strong>Số lượng:</strong> 1</p>
        </div>
        <div id="button-container">
          <button
            type="submit"
            id="create-payment-link-btn"
            style="
              width: 100%;
              background-color: blue;
              color: white;
              border: none;
              padding: 10px;
              font-size: 15px;
            "
          >
            Tạo Link thanh toán
          </button>
        </div>
      </div>
      <div id="embeded-payment-container" style="height: 350px"></div>
    </div>
  </body>
</html>
<script src="https://cdn.payos.vn/payos-checkout/v1/stable/payos-initialize.js"></script>
<script src="index.js"></script>