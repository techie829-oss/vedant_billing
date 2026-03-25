import axios from 'axios'

interface PincodeResponse {
    Message: string
    Status: string
    PostOffice: {
        Name: string
        Description: string | null
        BranchType: string
        DeliveryStatus: string
        Circle: string
        District: string
        Division: string
        Region: string
        Block: string
        State: string
        Country: string
        Pincode: string
    }[]
}

export const fetchPincodeDetails = async (pincode: string) => {
    if (pincode.length !== 6) return null;

    try {
        const response = await axios.get<PincodeResponse[]>(`https://api.postalpincode.in/pincode/${pincode}`)
        const data = response.data[0]

        if (data && data.Status === 'Success' && data.PostOffice && data.PostOffice.length > 0) {
            const office = data.PostOffice[0]
            return {
                city: office?.District || '',
                state: office?.State || '',
                country: office?.Country || ''
            }
        }
        return null
    } catch (error) {
        console.error('Failed to fetch pincode details:', error)
        return null
    }
}
