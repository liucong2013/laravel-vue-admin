import service from '@/utils/request'

// 创建SysAccessLogError
export const createSysAccessLogError = (data) => {
    return service({
        url: "/sysAccessLogError",
        method: 'POST',
        data
    })
}

// 更具ID或IDS 删除SysAccessLogError
export const deleteSysAccessLogError = (id) => {
    return service({
        url: `/sysAccessLogError/${id}`,
        method: 'DELETE',
    })
}

// 更新SysAccessLogError
export const updateSysAccessLogError = (id, data) => {
    return service({
        url: `/sysAccessLogError/${id}`,
        method: 'PUT',
        data
    })
}

// 根据idSysAccessLogError
export const findSysAccessLogError = (type) => {
    return service({
        url: `/sysAccessLogError/find/${type}`,
        method: 'GET',
    })
}

// 分页获取SysAccessLogError列表
export const getSysAccessLogErrorList = (params) => {
    return service({
        url: "/sysAccessLogError/list",
        method: 'get',
        params
    })
}
