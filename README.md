clean up files in s3

```
docker run --rm \
  -p 6001:6001 \
  -p 9601:9601 \
  -e SOKETI_DEBUG=1 \
  -e SOKETI_DEFAULT_APP_ID=acp \
  -e SOKETI_DEFAULT_APP_KEY=acp-key \
  -e SOKETI_DEFAULT_APP_SECRET=acp-secret \
  quay.io/soketi/soketi:1.0-16-debian
```
