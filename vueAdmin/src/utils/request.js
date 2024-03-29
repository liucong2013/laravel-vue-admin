import axios from 'axios'; // 引入axios
import { Message, Loading } from 'element-ui';
import { store } from '@/store/index'
const service = axios.create({
    baseURL: process.env.VUE_APP_BASE_API,
    timeout: 99999
})
let acitveAxios = 0
let loadingInstance
let timer
const showLoading = () => {
    acitveAxios++
    if (timer) {
        clearTimeout(timer)
    }
    timer = setTimeout(() => {
        if (acitveAxios > 0) {
            loadingInstance = Loading.service({ fullscreen: true })
        }
    }, 400);
}

const closeLoading = () => {
    acitveAxios--
    if (acitveAxios <= 0) {
        clearTimeout(timer)
        loadingInstance && loadingInstance.close()
    }
}
//http request 拦截器
service.interceptors.request.use(
    config => {
        // console.log(config)
        if (!config.donNotShowLoading) {
            showLoading()
        }
        const token = store.getters['user/token']
        const user = store.getters['user/userInfo']
        config.data = JSON.stringify(config.data);
        config.headers = {
            'Content-Type': 'application/json',
            'Authorization': "Bearer " + token,
            'x-user-id': user.id
        }
        return config;
    },
    error => {
        closeLoading()
        Message({
            showClose: true,
            message: error,
            type: 'error'
        })
        return Promise.reject(error);
    }
);


//http response 拦截器
service.interceptors.response.use(
    response => {
        closeLoading()
        if (response.headers["new-token"]) {
            store.commit('user/setToken', response.headers["new-token"])
        }
        if (response.data.code == 200 || response.headers.success === "true") {
            return response.data
        } else {
            if(response.config.responseType == 'blob' && response.status === 200 )
            {
                //兼容下载错误处理
                if(response.headers['content-type'] == 'application/json'){

                    let reader = new FileReader();
                    reader.readAsText(response.data);
                    reader.addEventListener("loadend", function () { //
                        let res = JSON.parse(reader.result); // 返回的数据
                        Message({
                            showClose: true,
                            message: res.msg || decodeURI(response.headers.msg),
                            type: 'error',
                        })
                        if (res.data && res.data.reload) {
                            store.commit('user/LoginOut')
                        }
                        return Promise.reject(res.msg)
                    });

                }else{
                    return response
                }

            }else{
                Message({
                    showClose: true,
                    message: response.data.msg || decodeURI(response.headers.msg),
                    type: 'error',
                })
                if (response.data.data && response.data.data.reload) {
                    store.commit('user/LoginOut')
                }
                return Promise.reject(response.data)
            }
        }
    },
    error => {
        closeLoading()
        Message({
            showClose: true,
            message: error,
            type: 'error'
        })
        return Promise.reject(error)
    }
)

export default service