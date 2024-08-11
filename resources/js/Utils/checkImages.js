const determineImageOrientation = (url) => {
    return new Promise((resolve) => {
    const img = new Image();
    img.onload = function() {
        resolve({ url, isLandscape: img.width >= img.height });
    };
    img.onerror = function() {
        resolve({ url, isLandscape: false, error: true });
    };
    img.src = url;
    });
};

export const checkImages = async (images) => {    
    const promises = images.map(determineImageOrientation);
    const results = await Promise.all(promises);
    return results.filter(result => !result.error && result.isLandscape).map(result => result.url);
};
