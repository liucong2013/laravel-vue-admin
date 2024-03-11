import service from '@/utils/request'

// 创建BaseArea
export const createCode = (data) => {
    return service({
        url: "/code",
        method: 'POST',
        data
    })
}




// 分页获取BaseArea列表
export const getCodeList = (params) => {
    return service({
        url: "/code/list",
        method: 'GET',
        params
    })
}

export const exportExcelCodeList = (data) => {
    return service({
        url: "/code/exportExcel",
        method: 'POST',
        responseType: "blob",
        data,
    })
}
