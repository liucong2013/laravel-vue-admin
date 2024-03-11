<template>
  <div>
    <!-- 查询表单开始 -->
    <div class="search-term">
      <el-form :inline="true" :model="searchInfo" class="demo-form-inline">

        <!--  <el-form-item>
          <el-button @click="onSubmit" type="primary">查询</el-button>
        </el-form-item> -->
        <!--        <el-form-item>-->
        <!--          <el-button @click="openDialog" type="primary">新增</el-button>-->
        <!--        </el-form-item>-->
        <el-form-item>
          <el-popover placement="top" v-model="deleteVisible" width="160">
            <p>确定要删除吗？</p>
            <div style="text-align: right; margin: 0">
              <el-button @click="deleteVisible = false" size="mini" type="text">取消</el-button>
              <el-button @click="onDelete" size="mini" type="primary">确定</el-button>
            </div>
            <el-button icon="el-icon-delete" size="mini" slot="reference" type="danger">批量删除</el-button>
          </el-popover>
        </el-form-item>
      </el-form>
    </div>
    <!-- 查询表单结束 -->

    <!-- 列表展示开始 -->
    <el-table :data="tableData" @selection-change="handleSelectionChange" border ref="multipleTable" stripe
              style="width: 100%" tooltip-effect="dark" @sort-change="sortChange" >
      <el-table-column type="selection" width="55"></el-table-column>

      <el-table-column label="请求方法" prop="method" width="160" sortable="custom">
        <el-table-column prop="method" width="160">
          <template slot="header" slot-scope="{}">
            <el-select v-model="searchInfo.method" size="mini" placeholder="选择请求方法" clearable @change="onSubmit">
              <el-option
                  v-for="item in methodValue"
                  :key="item.value"
                  :label="item.name"
                  :value="item.value"

              >
              </el-option>
            </el-select>

          </template>
        </el-table-column>
      </el-table-column>
      <el-table-column label="请求路径" prop="path"  sortable="custom">
        <el-table-column prop="path" width="190">
          <template slot="header" slot-scope="{}">
            <el-input v-model="searchInfo.path" size="mini" placeholder="搜索请求路径" @blur="onSubmit"/>
          </template>
        </el-table-column>
      </el-table-column>

      <el-table-column label="延迟（用时）" prop="latency" width="160" sortable="custom">
        <el-table-column prop="latency" width="160">
          <template slot="header" slot-scope="{}">
            <el-input v-model="searchInfo.latency" size="mini" placeholder="搜索延迟（用时）" @blur="onSubmit"/>
          </template>
        </el-table-column>
      </el-table-column>
      <el-table-column label="代理" prop="agent" width="160" sortable="custom">
        <el-table-column prop="agent" width="160">
          <template slot="header" slot-scope="{}">
            <el-input v-model="searchInfo.agent" size="mini" placeholder="搜索代理" @blur="onSubmit"/>
          </template>
          <template slot-scope="scope">
            <div>
              <el-popover placement="top-start" trigger="hover" v-if="scope.row.agent">
                <div class="popover-box">
                  <pre>{{ fmtBody(scope.row.agent) }}</pre>
                </div>
                <i class="el-icon-view" slot="reference"></i>
              </el-popover>
              <span v-else>无</span>
            </div>
          </template>
        </el-table-column>
      </el-table-column>

      <!--      <el-table-column label="错误信息" prop="error_message" width="160" sortable="custom">
              <el-table-column prop="error_message" width="160">
                <template slot="header" slot-scope="{}">
                  <el-input v-model="searchInfo.error_message" size="mini" placeholder="搜索错误信息" @blur="onSubmit"/>
                </template>
                <template slot-scope="scope">
                  <div>
                    <el-popover placement="top-start" trigger="hover" v-if="scope.row.error_message">
                      <div class="popover-box">
                        <pre>{{ fmtBody(scope.row.error_message) }}</pre>
                      </div>
                      <i class="el-icon-view" slot="reference"></i>
                    </el-popover>
                    <span v-else>无</span>
                  </div>
                </template>
              </el-table-column>

            </el-table-column>-->

      <el-table-column label="请求Body" prop="body" width="160" sortable="custom">
        <el-table-column prop="body" width="160">
          <template slot="header" slot-scope="{}">
            <el-input v-model="searchInfo.body" size="mini" placeholder="搜索请求Body" @blur="onSubmit"/>
          </template>
          <template slot-scope="scope">
            <div>
              <el-popover placement="top-start" trigger="hover" v-if="scope.row.body">
                <div class="popover-box">
                  <pre>{{ fmtBody(scope.row.body) }}</pre>
                </div>
                <i class="el-icon-view" slot="reference"></i>
              </el-popover>
              <span v-else>无</span>
            </div>
          </template>
        </el-table-column>
      </el-table-column>


      <el-table-column label="用户ID" prop="user_name" width="160" sortable="custom">
        <el-table-column prop="user_name" width="160">
          <template slot="header" slot-scope="{}">
            <el-input v-model="searchInfo.user_id" size="mini" placeholder="搜索用户ID" @blur="onSubmit"/>
          </template>

          <template slot-scope="scope">
            用户id: {{ scope.row.user_id }}
          </template>
        </el-table-column>
      </el-table-column>

      <el-table-column label="请求时间" prop="created_at" width="160" sortable="custom">
        <el-table-column prop="created_at" width="160" min-width="90">
          <template slot="header" slot-scope="{}">
            <el-date-picker
                clearable :style="{ width: '100%' }"
                v-model="searchInfo.created_at"
                type="date"
                size="mini"
                placeholder="搜索请求时间"
                @change="onSubmit"
                value-format="yyyy-MM-dd"
            >
            </el-date-picker>
          </template>
        </el-table-column>
      </el-table-column>

      <el-table-column label="按钮组" min-width="120">
        <template slot-scope="scope">
          <el-button circle @click="toDetileSysAccessLogError(scope.row)" size="small"
                     icon="el-icon-reading"></el-button>
          <el-button circle @click="updateSysAccessLogError(scope.row)" size="small"
                     icon="el-icon-edit-outline"></el-button>
          <el-popover placement="top" width="160" v-model="scope.row.visible">
            <p>确定要删除吗？</p>
            <div style="text-align: right; margin: 0">
              <el-button size="mini" type="text" @click="scope.row.visible = false">取消</el-button>
              <el-button type="primary" size="mini" @click="deleteSysAccessLogError(scope.row)">确定</el-button>
            </div>
            <el-button circle icon="el-icon-delete" size="mini" slot="reference"></el-button>
          </el-popover>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination background :current-page="page" :page-size="pageSize" :page-sizes="[10, 30, 50, 100]"
                   :style="{float:'right',padding:'20px'}" :total="total" @current-change="handleCurrentChange"
                   @size-change="handleSizeChange" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
    <!-- 列表展示结束 -->
    <!-- 增改表单开始 -->
    <el-dialog :before-close="closeDialog" :visible.sync="dialogFormVisible" title="表单操作">
      <el-form ref="elForm" :model="formData" :rules="rules" size="mini" label-position="left">
        <el-form-item label="请求ip" prop="title">
          <el-input v-model="formData.ip" placeholder="请输入请求ip" clearable :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="请求方法" prop="title">
          <el-input v-model="formData.method" placeholder="请输入请求方法" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="请求路径" prop="title">
          <el-input v-model="formData.path" placeholder="请输入请求路径" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="请求状态" prop="title">
          <el-input v-model="formData.status" placeholder="请输入请求状态" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="延迟（用时）" prop="title">
          <el-input v-model="formData.latency" placeholder="请输入延迟（用时）" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="代理" prop="title">
          <el-input v-model="formData.agent" placeholder="请输入代理" clearable :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="错误信息" prop="title">
          <el-input v-model="formData.error_message" placeholder="请输入错误信息" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="请求Body" prop="title">
          <el-input v-model="formData.body" placeholder="请输入请求Body" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="响应Body" prop="title">
          <el-input v-model="formData.resp" placeholder="请输入响应Body" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="用户id" prop="title">
          <el-input v-model="formData.user_id" placeholder="请输入用户id" clearable
                    :style="{ width: '100%' }"></el-input>
        </el-form-item>
        <el-form-item label="用户姓名" prop="title">
          <el-input v-model="formData.user_name" placeholder="请输入用户姓名" clearable
                    :style="{ width: '100%' }"></el-input>


        </el-form-item>

      </el-form>
      <div class="dialog-footer" slot="footer">
        <el-button @click="closeDialog">取 消</el-button>
        <el-button @click="enterDialog" type="primary">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 增改表单结束 -->


    <!-- 查看表单开始 -->
    <el-drawer :visible.sync="drawer" :with-header="true" size="80%" title="详情" v-if="drawer">
      <el-form ref="elForm" :model="drawerForm" label-position="top" size="mini" class="styled-form">
        <el-form-item label="请求ip" prop="ip"><span>{{ drawerForm.ip }}</span></el-form-item>
        <el-form-item label="请求方法" prop="method"><span>{{ drawerForm.method }}</span></el-form-item>
        <el-form-item label="请求路径" prop="path"><span>{{ drawerForm.path }}</span></el-form-item>
        <el-form-item label="请求状态" prop="status"><span>{{ drawerForm.status }}</span></el-form-item>
        <el-form-item label="延迟（用时）" prop="latency"><span>{{ drawerForm.latency }}</span></el-form-item>
        <el-form-item label="用户id" prop="user_id"><span>{{ drawerForm.user_id }}</span></el-form-item>
        <el-form-item label="用户姓名" prop="user_name"><span>{{ drawerForm.user_name }}</span></el-form-item>
        <el-form-item label="代理" prop="agent"><span>{{ drawerForm.agent }}</span></el-form-item>
        <el-form-item label="请求Body" prop="body">
          <span v-html="formattedBody"></span>
        </el-form-item>
        <el-form-item label="错误信息" prop="error_message">
          <span v-html="formattedErrorMessage"></span>
        </el-form-item>
      </el-form>
    </el-drawer>
    <!-- 查看表单结束 -->


  </div>
</template>

<script>
import {
  createSysAccessLogError,
  updateSysAccessLogError,
  findSysAccessLogError,
  getSysAccessLogErrorList,
  deleteSysAccessLogError
} from "@/api/system/sysAccessLogError.js";  //  此处请自行替换地址
import {formatTimeToStr} from "@/utils/data";
import infoList from "@/components/mixins/infoList";

export default {
  name: "SysAccessLogError",
  mixins: [infoList],
  data() {
    return {
      listApi: getSysAccessLogErrorList,
      dialogFormVisible: false,
      visible: false,
      drawer: false,
      // methodValue:this.returnData.method_all,
      methodValue: [],
      activeRow: [],
      type: "",
      deleteVisible: false,
      multipleSelection: [],
      formData: {
        ip: null,
        method: null,
        path: null,
        status: null,
        latency: null,
        agent: null,
        Error_message: null,
        body: null,
        resp: null,
        user_id: null,
        user_name: null,
      },
      rules: {
        ip: [{required: true, message: "请输入请求ip", trigger: "blur",}],
        method: [{required: true, message: "请输入请求方法", trigger: "blur",}],
        path: [{required: true, message: "请输入请求路径", trigger: "blur",}],
        status: [{required: true, message: "请输入请求状态", trigger: "blur",}],
        latency: [{required: true, message: "请输入延迟（用时）", trigger: "blur",}],
        agent: [{required: true, message: "请输入代理", trigger: "blur",}],
        error_message: [{required: true, message: "请输入错误信息", trigger: "blur",}],
        body: [{required: true, message: "请输入请求Body", trigger: "blur",}],
        resp: [{required: true, message: "请输入响应Body", trigger: "blur",}],
        user_id: [{required: true, message: "请输入用户id", trigger: "blur",}],
        user_name: [{required: true, message: "请输入用户姓名", trigger: "blur",}],
      },
      drawerForm: {
        ip: null,
        method: null,
        path: null,
        status: null,
        latency: null,
        agent: null,
        error_message: null,
        body: null,
        resp: null,
        user_id: null,
        user_name: null,
      },
    };
  },
  computed: {
    tableMaxHeight() {
      return window.innerHeight - 260 + 'px';
    },
    formattedBody() {
      return this.formatJson(this.drawerForm.body);
    },
    formattedErrorMessage() {
      return this.formatJson(this.drawerForm.error_message);
    }
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
    formatJson(jsonString) {
      try {
        const parsedJson = JSON.parse(jsonString);
        // Check if the parsed JSON has a 'trace' field
        if (parsedJson.trace) {
          // Replace newlines in the 'trace' field with HTML line breaks
          parsedJson.trace = '<br>' + parsedJson.trace.replace(/\n/g, '<br>');
        }
        // Format the JSON object with indentation
        const formattedJson = JSON.stringify(parsedJson, null, 2);
        return `<pre><code class="json">${formattedJson}</code></pre>`;
      } catch (error) {
        // If parsing fails, log the original string and return it
        console.log(jsonString);
        return jsonString;
      }
    },
    //条件搜索前端看此方法
    onSubmit() {
      this.page = 1
      this.pageSize = 10
      this.getTableData()
    },
    handleSelectionChange(val) {
      this.multipleSelection = val
    },
    async toDetileSysAccessLogError(row) {
      this.drawer = true;
      this.activeRow = row;
      const res = await findSysAccessLogError(row.id);
      if (res.code == 200) {
        this.drawerForm = res.data;
      }
    },
    async onDelete() {
      const ids = []
      this.multipleSelection &&
      this.multipleSelection.map(item => {
        ids.push(item.id)
      })
      const res = await deleteSysAccessLogError(JSON.stringify(ids))
      if (res.code == 200) {
        this.$message({
          type: 'success',
          message: '批量删除成功'
        })
        this.deleteVisible = false
        this.getTableData()
      }
    },
    async updateSysAccessLogError(row) {
      const res = await findSysAccessLogError(row.id);
      this.type = "update";
      if (res.code == 200) {
        this.formData = res.data;
        this.dialogFormVisible = true;
      }
    },
    closeDialog() {
      this.dialogFormVisible = false;
      this.formData = {
        ip: null,
        method: null,
        path: null,
        status: null,
        latency: null,
        agent: null,
        Error_message: null,
        body: null,
        resp: null,
        user_id: null,
        user_name: null,
      };
    },
    async deleteSysAccessLogError(row) {
      this.visible = false;
      const res = await deleteSysAccessLogError(row.id);
      if (res.code == 200) {
        this.$message({
          type: "success",
          message: "删除成功"
        });
        this.getTableData();
      }
    },
    async enterDialog() {
      let res;
      switch (this.type) {
        case "create":
          res = await createSysAccessLogError(this.formData);
          break;
        case "update":
          res = await updateSysAccessLogError(this.formData.id, this.formData);
          break;
        default:
          res = await createSysAccessLogError(this.formData);
          break;
      }
      if (res.code == 200) {
        this.$message({
          type: "success",
          message: "操作成功"
        })
        this.closeDialog();
        this.getTableData();
      }
    },
    openDialog() {
      this.type = "create";
      this.dialogFormVisible = true;
    },
    fmtBody(value) {
      try {
        return JSON.parse(value)
      } catch (err) {
        return value
      }
    },
    getTableDataAfter(data) {
      //data是请求的返回data字段
      this.methodValue = data.other.method_all;
    },
  },

  async created() {
    await this.getTableData();
  }
};
</script>

<style>
</style>

<style lang="scss">
.table-expand {
  padding-left: 60px;
  font-size: 0;

  label {
    width: 90px;
    color: #99a9bf;

    .el-form-item {
      margin-right: 0;
      margin-bottom: 0;
      width: 50%;
    }
  }
}

.popover-box {
  background: #112435;
  color: #f08047;
  height: 600px;
  width: 420px;
  overflow: auto;
  word-wrap: break-word;

}

.popover-box::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}

.el-drawer__body {
  overflow: auto;
  /* overflow-x: auto; */
}

.styled-form .el-form-item {
  margin-bottom: 15px;
  margin-left: 20px; /* Adjust this value as needed */
}

.styled-form .el-form-item__label {
  font-weight: bold;
}

.styled-form .el-form-item__content span {
  display: inline-block;
  max-width: 100%;
  overflow-wrap: break-word;
}

</style>
