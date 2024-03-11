/**
 * 列表与分页
 */
import { getDict } from "@/utils/dictionary";
export default {
    data() {
        return {
            page: 1,
            total: 10,
            pageSize: 10,
            tableData: [],
            searchInfo: {},
            customSort: '',
            returnData: {},
            form:{},


            //时间
            pickerOptions: {
                shortcuts: [{
                    text: '最近一周',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近三个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一年',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 365);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '本月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setDate(1);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '上个月',
                    onClick(picker) {
                        const date = new Date();
                        const start = new Date(date.getFullYear() , date.getMonth() -1 , 1);
                        const end = new Date(date.getFullYear() , date.getMonth() , 0);
                        picker.$emit('pick', [start, end]);
                    }
                }
                ]
            },

        }
    },
    methods: {
        filterDict(value, type) {
            const rowLabel = this[type + "Options"] && this[type + "Options"].filter(item => item.value == value)
            return rowLabel && rowLabel[0] && rowLabel[0].label
        },
        sortChange(column) {
            if (column.order === 'ascending') {
                this.customSort = column.prop
            } else if (column.order === 'descending') {
                this.customSort = '-' + column.prop
            } else {
                this.customSort = ''
            }
            this.getTableData()
        },
        async getDict(type) {
            const dicts = await getDict(type)
            this[type + "Options"] = dicts
        },
        handleSizeChange(val) {
            this.pageSize = val
            this.getTableData()
        },
        handleCurrentChange(val) {
            this.page = val
            this.getTableData()
        },
        //获取列表
        async getTableData(page = this.page, pageSize = this.pageSize, customSort = this.customSort) {

            //忽略请求参数中的key,这个key传到后端会导致后端报错
            let keyBreak = ['isUseCache']


            if (customSort === null) {
                customSort = '';
            }

            let searchOther = {};


            for (let key in this.$route.params) {
                if(keyBreak.includes(key)){
                    break
                }

                if (this.$route.params[key]  || this.$route.params[key]  === null ){
                    if(this.$route.params[key]  !== null){
                        let ifValue = Number(this.$route.params[key]);
                        console.log(isNaN(ifValue) , this.$route.params[key])
                        if(isNaN(ifValue)){
                            this.$route.params[key] = parseInt(this.$route.params[key]);
                        }
                        console.log(777 , this.$route.params[key])
                    }

                    this.$set(searchOther, key, this.$route.params[key]);
                    this.$set(this.searchInfo, key, this.$route.params[key]);
                    // this.searchInfo[key] = this.$route.params[key];
                    //设置为undefined后,就不会出现用户修改值后无效的情况
                     this.$route.params[key] = undefined;
                }

            }

            for (let key in this.$route.query) {
                if(keyBreak.includes(key)){
                    break
                }
                if (this.$route.query[key] || this.$route.query[key]  === null ) {
                    if(this.$route.query[key]  !== null){
                        let ifValue = Number(this.$route.query[key]);
                        if(isNaN(ifValue)){
                            this.$route.query[key] = parseInt(this.$route.query[key]);
                        }
                    }

                    this.$set(searchOther, key, this.$route.query[key]);
                    // this.searchInfo[key] = this.$route.params[key];
                    //设置为undefined后,就不会出现用户修改值后无效的情况,这里query的数据不能清空,清空后会出现页面上用了这个判断,被清空后页面判断失效的情况
                    // this.$route.query[key] = undefined;

                }
            }




            let search = {
                customSearch : []
            }

            for (let key in this.form) {
                if (this.form[key] || this.form[key] === 0){
                    this.$set(search.customSearch, key, this.form[key]);
                    search['customSearch['+key+']'] = this.form[key]
                    search.customSearch[key] = this.form[key]

                    this.$set(searchOther, key, this.form[key]);
                }
            }

            for (let key in this.searchInfo) {
                if (this.searchInfo[key] || this.searchInfo[key] === 0){
                    this.$set(search.customSearch, key, this.searchInfo[key]);
                    search['customSearch['+key+']'] = this.searchInfo[key]
                    search.customSearch[key] = this.searchInfo[key]

                    this.$set(searchOther, key, this.searchInfo[key]);
                }
            }

            for (let key in searchOther) {
                if (searchOther[key] || searchOther[key] === 0){
                    this.$set(search.customSearch, key, searchOther[key]);
                    search['customSearch['+key+']'] = searchOther[key]
                    search.customSearch[key] = searchOther[key]
                }
            }


            const table = await this.listApi({ page, pageSize , customSort , ...search })
            this.tableData = table.data.list
            this.total = table.data.total
            this.page = table.data.page
            this.pageSize = table.data.pageSize
            this.returnData = table.data
            //  this.customSort = table.data.customSort

            this.getTableDataAfter(table.data);
        },
        getTableDataAfter(data) {
            this.returnData = data
        },
        //根据后台数据获取内容
        getTypeText(type , typeData) {

            let returnValue = '未知';

            if(type == null){
                return type;
            }

            for (const i in typeData) {
                if (typeData[i].value === type) {
                    returnValue = typeData[i].name
                    break
                }
            }
            return returnValue;

        },
        //多图追加前缀
        addTextArr(arrText , arr)
        {
            let newArr = []
            for(var i=0;i<arr.length;i++){
                newArr.push(arrText+arr[i])
            }
            return newArr;
        },
    },
    watch: {
        $route() {
            this.searchInfo = {};
        }
    },
}
