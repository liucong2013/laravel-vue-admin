<template>
  <div>
    <!-- 查询表单开始 -->
    <div class="search-term">
      <el-form :inline="true" :model="searchInfo" class="demo-form-inline">
        <el-form-item>
          <el-input v-model="searchInfo.codeNumber" placeholder="查询编码" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>

        <el-form-item>
          <el-button @click="onSubmit" type="primary">查询</el-button>
        </el-form-item>
        <el-form-item>
          <el-button @click="openDialog" type="success">生成二维码</el-button>
        </el-form-item>
        <el-form-item>
          <el-button @click="openDialog1" type="warning" icon="el-icon-download">下载二维码excel</el-button>
        </el-form-item>

      </el-form>
    </div>
    <!-- 查询表单结束 -->
    <!-- 列表展示开始 -->
    <el-table :data="tableData" @selection-change="handleSelectionChange" border ref="multipleTable" stripe
              style="width: 100%" tooltip-effect="dark">

      <el-table-column label="编号" prop="code"></el-table-column>
      <el-table-column label="批次" prop="batch" width="80"></el-table-column>

    </el-table>
    <el-pagination :current-page="page" :page-size="pageSize" :page-sizes="[10, 30, 50, 100]"
                   :style="{float:'right',padding:'20px'}" :total="total" @current-change="handleCurrentChange"
                   @size-change="handleSizeChange" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
    <!-- 列表展示结束 -->
    <!-- 增改表单开始 -->
    <el-dialog :before-close="closeDialog" :visible.sync="dialogFormVisible" title="生成二维码">
      <el-form ref="elForm" :model="formData"  size="mini" label-width="100px" label-position="left">
        <el-form-item label="生成数量" prop="title">

          <el-input v-model="formData.num" placeholder="请输入二维码数量"  clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item >

        请勿一次性生成太多,200以内就好
        </el-form-item>

      </el-form>
      <div class="dialog-footer" slot="footer">
        <el-button @click="closeDialog">取 消</el-button>
        <el-button @click="enterDialog" type="primary">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 增改表单结束 -->

    <el-dialog :before-close="closeDialog1" :visible.sync="dialogFormVisible1" title="生成二维码">
      <el-form ref="elForm" :model="formData1"  size="mini" label-width="100px" label-position="left">
        <el-form-item label="批次" prop="title">

          <el-input v-model="formData1.batch" placeholder="请输入批次"  clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>


      </el-form>
      <div class="dialog-footer" slot="footer">
        <el-button @click="closeDialog1">取 消</el-button>
        <el-button @click="enterDialog1" type="primary">确 定</el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>

import {formatTimeToStr} from "@/utils/data";
import infoList from "@/components/mixins/infoList";
import {createCode, exportExcelCodeList, getCodeList} from "@/api/code";

export default {
  name: "encodedCode",
  mixins: [infoList],
  data() {
    return {
      listApi: getCodeList,
      dialogFormVisible: false,
      dialogFormVisible1:false,
      visible: false,
      type: "",
      deleteVisible: false,
      multipleSelection: [],
      formData: {
       num:100,
      },
      formData1: {
        batch:null,
      },
    };
  },
  filters: {
    formatDate: function (time) {
      if (time != null && time != "") {
        var date = new Date(time);
        return formatTimeToStr(date, "yyyy-MM-dd hh:mm:ss");
      } else {
        return "";
      }
    },
    formatBoolean: function (bool) {
      if (bool != null) {
        return bool ? "是" : "否";
      } else {
        return "";
      }
    },
  },
  methods: {
    //条件搜索前端看此方法
    onSubmit() {
      this.page = 1
      this.pageSize = 10
      this.getTableData()
    },
    handleSelectionChange(val) {
      this.multipleSelection = val
    },

    closeDialog1() {
      this.dialogFormVisible1 = false;
    },


    closeDialog() {
      this.dialogFormVisible = false;
    },

    async enterDialog() {
      let res;
      res = await createCode(this.formData);
      if (res.code == 200) {
        this.$message({
          type: "success",
          message: "操作成功"
        })
        this.closeDialog();
        this.getTableData();
      }
    },

    async enterDialog1() {
      await exportExcelCodeList( this.formData1)
          .then(res => {

            if(res.headers['content-type'] != 'application/json'){
              this.$message({
                message: '请耐心等待下载',
                type: 'success'
              });

              let blob = new Blob([res.data]);
              // 通过 URL.createObjectURL(Blob对象), 可以把 Blob对象 转换成一个链接地址,该地址可以直接用在某些 DOM 的 src 或者 href 上
              var downloadElement = document.createElement('a');
              var href = window.URL.createObjectURL(blob); //创建下载的链接
              downloadElement.href = href;
              downloadElement.download = window.decodeURI(res.headers['filename'], "UTF-8"); //下载后文件名

              document.body.appendChild(downloadElement);
              downloadElement.click(); //点击下载
              document.body.removeChild(downloadElement); //下载完成移除元素
              window.URL.revokeObjectURL(href); //释放掉blob对象
            }



          })
          .catch(error => {
            console.log(error , 222);
          });
    },


    openDialog() {
      this.type = "create";
      this.dialogFormVisible = true;
    },
    openDialog1() {
      this.dialogFormVisible1 = true;
    }
  },
  async created() {
    await this.getTableData();
  }
};
</script>

<style>
</style>